<?php 
 echo "## API \"" . $template_data["title"] ."\"\n\n";
 
 echo "   in route : \"". $template_data["route"] . "\n\n";
 
 foreach($template_data["contents"] as $data) { 
    echo "Route | Method | function | Description\n";
    echo "------|--------|----------|------------\n";

     if (sizeof($data["route_data"]) == 4) { 
       echo "`" . $data["route_data"][2]."` | ". strtoupper($data["route_data"][1]) . " | " . $data["route_data"][3] . " | " . $data["doc"]["description"] ."\n";
     }

     if(!empty($data["doc"]["params"])) { 
       echo "\n";
   
       foreach ($data["doc"]["params"] as $param) { 
   
         if (sizeof($param) == 4) {
           echo "* `{" . $param[1] ."}`->`" . $param[2] ."` :: ". $param[3] . "\n";
         }
       }
     }
     echo "\n---\n"; 
 }

