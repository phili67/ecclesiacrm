<?php  
      function xml2array ( $xmlObject, $out = array () )  
      {  
           foreach ( (array) $xmlObject as $index => $node )  
                $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;  
           return $out;  
      }  
   $client = new SoapClient(null,  
     array(  
       'location' => "https://mail.epis-strasbourg.eu:7070/service/admin/soap",  //YOUR ZIMBRA SOAP URL",  
       'uri' => "urn:zimbraAccount",  //urn:zimbraAccount
       'trace' => 1,  
       'exceptions' => 1,  
       'soap_version' => SOAP_1_1,  
       'style' => SOAP_RPC,  
       'use' => SOAP_LITERAL  
     )  
   );  
   echo "Client Built<br>";  
      //Provide username (eg. mm@zimbra.testdomain.com) and password       
      $var = new SoapVar('<account by="name">philippe.logel@epis-strasbourg.eu</account><password>#Alias1</password>', XSD_ANYXML);            
      $params = array(  
                $var,  
                );                 
   echo "Params built<br>";  
   try {  
     echo "Creating header<br>";  
     $soapHeader = new SoapHeader(  
           'urn:zimbraAccount',  
           'context'  
           );  
     echo "Authorizing<br>";      
     $result = $client->__soapCall(  
           "AuthRequest",   
           $params,   
           null,  
           $soapHeader  
     );  
     echo "Authorized<br>";    
           $authToken=$result['authToken'];  
     //print_r($result);  
                try {  
                     echo "Trying to get Folders<br>";  
                     $soapHeader = new SoapHeader(  
                                    'urn:zimbraMail',  
                                    'context',  
                                         new SoapVar(  
                                              '<ns1:context><format type="xml" /></ns1:context>',  
                                              XSD_ANYXML  
                                         )                                     
                                    );  
                     $result = $client->__soapCall(  
                                    "GetFolderRequest",   
                                    array(''),   
                                    array('uri' => 'urn:zimbraMail'),  
                                    $soapHeader  
                     );  
                     echo "Got folder info<br>";    
                     $xml = new SimpleXMLElement($client->__getLastResponse());  
                     $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');  
                     $body = $xml->xpath('//soap:Body');  
                     $xml->registerXPathNamespace('zimbra', 'urn:zimbraMail');  
                     $GetFolderResponse = $xml->xpath('//soap:Body/zimbra:GetFolderResponse');  
                     $folder = $GetFolderResponse[0]->folder;  
                     $folder->registerXPathNamespace('zimbra', 'urn:zimbraMail');  
         $appointments = $folder->xpath('zimbra:folder[@view="appointment"]');  
                     $links = $folder->xpath('zimbra:link[@view="appointment"]');  
                     //print_r($appointments);  
                     //print_r($links);  
                     $appointments = xml2array ( $appointments, $out = array () );  
                     $links = xml2array ( $links, $out = array () );  
                     echo "<pre/>";  
                     print_r($appointments);  
                     print_r($links);  
                     //echo htmlentities($client->__getLastRequest()) . "<br><br>";  
         //echo htmlentities($client->__getLastResponse()) . "<br><br>";  
                } catch (SoapFault $exception) {  
                     echo "exception caught while trying to fetch Folder info<br><br>";  
                     //echo htmlentities($client->__getLastRequest()) . "<br><br>";  
                  // echo htmlentities($client->__getLastRequestHeaders()) . "<br><br>";  
                  // echo htmlentities($client->__getLastResponseHeaders()) . "<br><br>";  
                     echo htmlentities($client->__getLastResponse()) . "<br><br>";  
                     //echo $exception . "<br><br>";  
                }  
   } catch (SoapFault $exception) {  
     echo "exception caught while trying to authorize<br><br>";  
     //echo htmlentities($client->__getLastRequest()) . "<br><br>";  
     // echo htmlentities($client->__getLastRequestHeaders()) . "<br><br>";  
     //echo htmlentities($client->__getLastResponseHeaders()) . "<br><br>";  
     echo htmlentities($client->__getLastResponse()) . "<br><br>";  
     //echo $exception . "<br><br>";  
   }  
 ?>  