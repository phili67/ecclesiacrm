#!/usr/bin/php
<?php
    $options = getopt("i:o:n::h");
    $doc_cache = array();
    $doc_temp = emptyTemp();

    $use_template = array_key_exists("t", $options) ? $options["t"] : "markdown";
    $title = array_key_exists("n", $options) ? $options["n"] : "API Description";

    //echo "Title ${title}\n";

    if ($argc < 3 || array_key_exists("h", $options)) {
        printUsage();
        die();
    }

    if (!file_exists($options["i"])) {
        die("Input file does not exist !\n");
    }

    $route = str_replace("../src","",$options["i"]);

    $out_file = fopen($options["o"], "w");
    if (!$out_file) {
        die("Could not create output file. \n");
    }

    $src_file = fopen($options["i"], 'r');
    if (!empty($src_file)){
        while(($line = fgets($src_file)) !== false){
            preg_match("/\*\s+@!\s+(.*)/", $line, $description);
            if ($description){
                $doc_temp["description"] = $description[1];
                continue;
            }

            /* Matches route parameters ( #! ) */
            preg_match("/\*\s+#!\s+param:\s+([a-zA-Z_1-2]*)->([a-z]*)\s+::\s+(.*)/", $line, $params);
            if($params){
                array_push($doc_temp["params"], $params);
                continue;
            }

            /* Matches route path ( src ) */
            //preg_match("/[a-z]*->(get|post|put|delete)\(/", $line, $route_data);
              // slash at the end is the end of the reg search
              // extract this->get( this->post( etc ...
            preg_match("/\\$[a-z]*->(get|post|put|delete)\(\'(.*)\', \'(.*)\' \)/", $line, $route_data);
              // extract this->get('coucou', 'toto' )


            if ($route_data) {
                $doc_cache[] = array(
                    "route_data" => $route_data,
                    "doc" => $doc_temp
                );
                $doc_temp = emptyTemp();
            } else {
              preg_match("/\\$[a-z]*->(get|post|put|delete)\(\'(.*)\', \"(.*)\" \)/", $line, $route_data);

              if ($route_data) {
                $doc_cache[] = array(
                    "route_data" => $route_data,
                    "doc" => $doc_temp
                );
                $doc_temp = emptyTemp();
              } else {
                preg_match("/\\$[a-z]*->(get|post|put|delete)\(\'(.*)\', (.*) \{/", $line, $route_data);
                if ($route_data) {
                  $doc_cache[] = array(
                      "route_data" => $route_data,
                      "doc" => $doc_temp
                  );
                  $doc_temp = emptyTemp();
                } else {
                    preg_match("/\\$[a-z]*->(get|post|put|delete)\(\'(.*)\', (.*) \)/", $line, $route_data);
                    if ($route_data) {
                        $doc_cache[] = array(
                            "route_data" => $route_data,
                            "doc" => $doc_temp
                        );
                        $doc_temp = emptyTemp();
                    }
                }
              }
            }
        }

        //print_r($doc_cache)."\n";

        //echo $route;

        fwrite($out_file,
            template(dirname(__FILE__) . "/templates/". $use_template .".php",
                array(
                    "title" => $title,
                    "route" => $route,
                    "contents" => $doc_cache)));
    } else {
        die("Could not open input file");
    }

    fclose($out_file);
    fclose($src_file);


    /* Declarations */

    function template($filename, $template_data){
        ob_start();
        if (file_exists($filename)){
            include($filename);
        }

        return ob_get_clean();
    }

    function emptyTemp(){
        return array(
            "description" => "No description",
            "params" => Array()
        );
    }

    function printUsage(){
        print("Slim simple documentation generator.\n");
        print("Usage: slim_doc.php -i[input_file] -o[output_file]\n");
        print("Optional Parameters: \n\t-t[template] ( default : markdown )\n");
        print("\t-n[name] ( API Name header )\n");
        print("\t-h ( show help )\n");
    }
?>
