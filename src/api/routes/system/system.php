<?php

use EcclesiaCRM\dto\SystemURLs;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$app->group('/system', function () {
  $this->post('/csp-report', function ($request, $response, $args) {
          $input = json_decode($request->getBody());
          $log  = json_encode($input, JSON_PRETTY_PRINT);
          $this->Logger->warn($log);
  });
  
  $this->post('/deletefile', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
         
        if ( isset ($params->name) && isset($params->path) ) {
          if (unlink(SystemURLs::getDocumentRoot().$params->path.$params->name)) {
            return $response->withJson(['status' => "success"]);
          }
        }
        
        return $response->withJson(['status' => "failed"]);
  });  
});
