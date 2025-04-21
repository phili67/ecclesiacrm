<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated     : 2018/05/13
//

namespace EcclesiaCRM\CardDav;

use Sabre\DAV;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\CardDAV\Plugin;
use Sabre\VObject;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\UserQuery;

use Sabre\CardDAV\IAddressBook;



use Sabre\CardDAV\VCFExportPlugin as VCFExportPlugin;

class VCFExportPluginExtension extends VCFExportPlugin {
    public function initialize(DAV\Server $server)
    {
        $this->server = $server;
        $this->server->on('method:GET', [$this, 'httpGet'], 90);
        $server->on('browserButtonActions', function ($path, $node, &$actions) {
            if ($node instanceof IAddressBook) {
                $actions .= '<a href="'.htmlspecialchars($path, ENT_QUOTES, 'UTF-8').'?export"><span class="oi" data-glyph="book"></span></a>';
            }
        });
    }

    /**
     * Intercepts GET requests on addressbook urls ending with ?export.
     *
     * @return bool
     */
    public function httpGet(RequestInterface $request, ResponseInterface $response)
    {
        $queryParams = $request->getQueryParameters();
        if (!array_key_exists('export', $queryParams)) {
            return;
        }

        $path = $request->getPath();

        $node = $this->server->tree->getNodeForPath($path);

        $infos = $node->getProperties(['id','access','addressbookid']);

        $shared = false;
        $addressbookId = null;
        if (array_key_exists('access', $infos)) {
            // we are in a case of a shared cards 
            $shared = true;

            // we have to retreive the right calendar
            $addressbookid = $infos['addressbookid'];

            $pdo = Bootstrapper::GetPDO();            
            $addressBooksTableName = 'addressbooks';

            // all the addressbooks are shared from the administrators
            $userAdmin = UserQuery::Create()->findOneByPersonId(1);          

            $stmt = $pdo->prepare('SELECT id, uri, displayname, principaluri, description, synctoken FROM '.$addressBooksTableName.' WHERE principaluri = ? and id = ?');
            $stmt->execute(['principals/'.strtolower($userAdmin->getUserName()), $addressbookid]);

            $row = $stmt->fetch();                
            $path = 'addressbooks/'.strtolower($userAdmin->getUserName()).'/' . $row['uri'];
            $addressbookId = $row['id'];
            $addressbookUri = $row['uri'];
            $node = $this->server->tree->getNodeForPath($path);            
        }

        if (!($node instanceof IAddressBook)) {
            return;
        }

        $this->server->transactionType = 'get-addressbook-export';

        // Checking ACL, if available.
        if ($aclPlugin = $this->server->getPlugin('acl')) {
            $aclPlugin->checkPrivileges($path, '{DAV:}read');
        }

        if ($shared) {
            $cardav = $this->server->getPlugin('carddav');

            $cardsTableName = 'cards';

            $pdo = Bootstrapper::GetPDO(); 
            $stmt = $pdo->prepare('SELECT id, carddata, uri, lastmodified, etag, size FROM '.$cardsTableName.' WHERE addressbookid = ?');
            $stmt->execute([$addressbookId]);

            $nodes = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!is_null($row['carddata'])) {
                    $nodes[] = [
                        "200" => [
                            "{urn:ietf:params:xml:ns:carddav}address-data" => $row['carddata']
                        ],
                        "400" => [                            
                        ],
                        "href" => "addressbooks/admin/". $addressbookUri."/".$row['uri']
                    ];
                } else {
                    $nodes[] = [
                        "200" => [                        
                        ],
                        "400" => [
                            "{urn:ietf:params:xml:ns:carddav}address-data" => null
                        ],
                        "href" => "addressbooks/admin/". $addressbookUri."/".$row['uri']
                    ];

                }
            }
        } else {
            $nodes = $this->server->getPropertiesForPath($path, [
                '{'.Plugin::NS_CARDDAV.'}address-data',
            ], 1);    
        }
        
        $format = 'text/directory';

        

        
        $output = null;
        $filenameExtension = null;

        switch ($format) {
            case 'text/directory':
                $output = $this->generateVCF($nodes);
                $filenameExtension = '.vcf';
                break;
        }

        $filename = preg_replace(
            '/[^a-zA-Z0-9-_ ]/um',
            '',
            $node->getName()
        );
        $filename .= '-'.date('Y-m-d').$filenameExtension;

        $response->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->setHeader('Content-Type', $format);

        $response->setStatus(200);
        $response->setBody($output);

        // Returning false to break the event chain
        return false;
    }

    /**
     * Merges all vcard objects, and builds one big vcf export.
     *
     * @return string
     */
    public function generateVCF(array $nodes)
    {
        $output = '';

        foreach ($nodes as $node) {
            if (!isset($node[200]['{'.Plugin::NS_CARDDAV.'}address-data'])) {
                continue;
            }
            $nodeData = $node[200]['{'.Plugin::NS_CARDDAV.'}address-data'];

            // Parsing this node so VObject can clean up the output.
            $vcard = VObject\Reader::read($nodeData);
            $output .= $vcard->serialize();

            // Destroy circular references to PHP will GC the object.
            $vcard->destroy();
        }

        return $output;
    }
}
