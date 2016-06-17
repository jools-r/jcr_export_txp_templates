<?php
/*
  TODO: 
    – provide "choose what to export" option
    – making "naming scheme" an option in the ui
*/


    /**
     * Plugin config variables
     */

    global $jcr_export_txp_templates_prefs;
    
    $jcr_export_txp_templates_prefs = array(
        'base_dir'           =>  'templates/export',
        'form_naming_scheme' =>  'name_type',  // switch to 'type_name' if preferred

        'subdir_section' 	   =>	 'sections',
        'subdir_plugins' 	   =>	 'plugins',
        'subdir_pages'       =>  'pages',
        'subdir_forms'       =>  'forms',
        'subdir_css'         =>  'styles',
        'subdir_variables'   =>  'variables',

        'ext_section'        =>  '.json',
        'ext_plugins'        =>  '.txt',
        'ext_pages'          =>  '.txp',
        'ext_forms'          =>  '.txp',
        'ext_css'            =>  '.css',
        'ext_variables'      =>  '.json'
    );



    /**
     * Plugin instantiation 
     */
 
    if (@txpinterface == 'admin') {

        add_privs('jcr_export_txp_templates', '1,2');
        register_tab('extensions', 'jcr_export_txp_templates', gTxt('jcr_export_txp_tabname'));
        register_callback('jcr_dispatcher', 'jcr_export_txp_templates');
    }



    /**
     * Act on step actions: only "export" or main ui
     */

    function jcr_dispatcher($event, $step)
    {
       // true requires a valid txp_token or the action will fail.
       $available_steps = array(
          'jcr_export_templates'  => true
       );
    
       // If nothing matches, define a default action.
       if (!$step or !bouncer($step, $available_steps)) {
          $step = 'jcr_export_ui_pane';
       }
    
       // Run the function.
       $step();
    }
    
   
    
    /**
     * Main interface – option to set export directory name
     */

    function jcr_export_ui_pane($msg = '')
    {
       pagetop(gTxt('jcr_export_txp_pageheader'), $msg);
    
       print "
           <h1>" . gTxt('jcr_export_txp_pageheader') . "</h1>
       ".form(
             graf(gTxt('jcr_export_txp_name_export').
             fInput('text', 'jcr_export_dir', '').
             fInput('submit', 'go', gTxt('jcr_export_txp_export_btn'), 'smallerbox').
             eInput('jcr_export_txp_templates').sInput('jcr_export_templates')
           )
       );

    }


    /**
     * Export the templates and report on progress.
     */

    function jcr_export_templates($msg = '')
    {
       global $prefs;

       pagetop(gTxt('jcr_export_txp_pageheader'), $msg);
       
       $template = new jcr_export_txp_template(); 
       $dir = trim(sanitizeForUrl(ps('jcr_export_dir')), '-');
       $template->export($dir);
  
    }


    /**
     * Export class
     */

    class jcr_export_txp_template {

        function jcr_export_txp_template() {

            global $prefs, $jcr_export_txp_templates_prefs;

            $this->_config = $jcr_export_txp_templates_prefs;


            /* internal config variables */
            
            $this->_config['full_base_path'] = $prefs['path_to_site'] . '/' . $this->_config['base_dir'];                                     

            $this->exportTypes = array(
                "plugins" =>  array(
                                "ext"        =>  $this->_config['ext_plugins'],
                                "data"       =>  "code",
                                "fields"     =>  "name, status, author, author_uri, version, description, help, code, code_restore, code_md5, type",
                                "nice_title" =>  gTxt('jcr_export_txp_plugins_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_plugins'),
                                "sql"        =>  "`status` = %d, `author` = '%s', `author_uri` = '%s', `version` = '%s', `description` = '%s', `help` = '%s', `code` = '%s', `code_restore` = '%s', `code_md5` = '%s', `type` = %d",
                                "subdir"     =>  $this->_config['subdir_plugins'],
                                "table"      =>  "txp_plugin",
                                "filter"     =>  "`status` = 1"
                            ),
                "pages" =>  array(
                                "ext"        =>  $this->_config['ext_pages'],
                                "data"       =>  "user_html",
                                "fields"     =>  "name, user_html",
                                "nice_title" =>  gTxt('jcr_export_txp_pages_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_pages'),
                                "sql"        =>  "`user_html` = '%s'",
                                "subdir"     =>  $this->_config['subdir_pages'],
                                "table"      =>  "txp_page",
                                "filter"     =>  "1=1"
                            ),
                "forms" =>  array(
                                "ext"        =>  $this->_config['ext_forms'],
                                "data"       =>  "Form",
                                "fields"     =>  "name, type, Form",
                                "nice_title" =>  gTxt('jcr_export_txp_forms_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_forms'),
                                "sql"        =>  "`Form` = '%s', `type` = '%s'",
                                "subdir"     =>  $this->_config['subdir_forms'],
                                "table"      =>  "txp_form",
                                "filter"     =>  "1=1"
                            ),
                "css"   =>  array(
                                "ext"        =>  $this->_config['ext_css'],
                                "data"       =>  "css",
                                "fields"     =>  "name, css",
                                "nice_title" =>  gTxt('jcr_export_txp_styles_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_styles'),
                                "sql"        =>  "`css` = '%s'",
                                "subdir"     =>  $this->_config['subdir_css'],
                                "table"      =>  "txp_css",
                                "filter"     =>  "1=1"
                            ),
                "section" =>  array(
                                "ext"        =>  $this->_config['ext_section'],
                                "data"       =>  "name",
                                "fields"     =>  "name, page, css, in_rss, on_frontpage, searchable, title",
                                "nice_title" =>  gTxt('jcr_export_txp_sections_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_sections'),
                                "sql"        =>  "`title` = '%s', `page` = '%s', `in_rss` = %d, `on_frontpage` = %d, `searchable` = %d",
                                "subdir"     =>  $this->_config['subdir_section'],
                                "table"      =>  "txp_section",
                                "filter"     =>	"1=1"
                            ),
                "variable" =>  array(
                                "ext"        =>  $this->_config['ext_variables'],
                                "data"       =>  "val",
                                "fields"     =>  "name, val, type, event, html, position",
                                "nice_title" =>  gTxt('jcr_export_txp_variables_title'),
                                "nice_name"  =>  gTxt('jcr_export_txp_variables'),
                                "sql"        =>  "`name` = '%s', `val` = '%s', `type` = %d, `event` = '%s', `html` = '%s', `position` = %d",
                                "subdir"     =>  $this->_config['subdir_variables'],
                                "table"      =>  "txp_prefs",
                                "filter"     =>	"`event` = 'oui_flat_var'"
                            )

            );
        }

        /**
         * Check if directory exists and is writable and report error if not
         */
         
         function checkdir($dir = '') {
  
            $msg = '';
            $dir = $this->_config['full_base_path'] . '/' . $dir;

            $outputDir =  array(
                            $dir,
                            $dir.'/'.$this->_config['subdir_plugins'],
                            $dir.'/'.$this->_config['subdir_pages'],
                            $dir.'/'.$this->_config['subdir_css'],
                            $dir.'/'.$this->_config['subdir_forms'],
                            $dir.'/'.$this->_config['subdir_section'],
                            $dir.'/'.$this->_config['subdir_variables']
                        );

            foreach ($outputDir as $curDir) {
 
                if (!is_dir($curDir)) {
                    if (!@mkdir($curDir, 0777)) {
                        $msg = array( gTxt('jcr_export_txp_dir_missing', array('{path}' => $curDir)), E_ERROR);
                        break;
                    }
                }
                if (!is_writable($curDir)) {
                    $msg = array( gTxt('jcr_export_txp_dir_not_writable', array('{path}' => $curDir)), E_ERROR);
                    break;
                }
            }
            
            return $msg;

        }



        /**
         * Check if directory exists and is writable and report error if not
         */

        function export($dir = '') {

            // if directory does not exist or is non-writable, abort      
            $dir_problem = $this->checkdir($dir);
            
            if ($dir_problem != '') {
                return jcr_export_ui_pane($dir_problem);
            }

            // report successful exports and any errors
            foreach ($this->exportTypes as $type => $config) {
                print "
                    <h1>" . gTxt('jcr_export_txp_exporting') . " " . $config['nice_title'] . "</h1>
                    <ul class='results'>
                ";

                $rows = safe_rows( $config['fields'], $config['table'], $config['filter'] );

                foreach ($rows as $row) {
                
                    if ($type == 'plugins') {
                        $file_name = $row['name'] . (isset($row['version']) ? ".".$row['version'] : "");
                    } else if ($type == 'forms') {
                        if ($this->_config['form_naming_scheme'] == 'name_type') {
                            $file_name = $row['name'] . (isset($row['type']) ? ".".$row['type'] : "");
                        } else {
                            $file_name = (isset($row['type']) ? ".".$row['type'] : "") . $row['name'];
                        }
                    } else {
                        $file_name = $row['name'];
                    }

                    $filename     =   sprintf(
                                          "%s/%s/%s/%s%s",
                                          $this->_config['full_base_path'],
                                          $dir,
                                          $config['subdir'],
                                          $file_name,
                                          $config['ext']
                                      );
                    $nicefilename =   sprintf(
                                          "/%s/%s/%s%s",
                                          $dir,
                                          $config['subdir'],
                                          $file_name,
                                          $config['ext']
                                      );

										$data = '';
										// if plugin, base64 encode
                    if ($type=='plugins') {
                      $data = chunk_split(base64_encode(serialize($row)), 72);
                    }
                    // if section, make json
                    else if ($type=='section' || $type=='variable') {
                      $data = json_encode($row, JSON_PRETTY_PRINT);
                    // if other, then get specific data
                    } else {
                    	$data = $row[$config['data']];
                    }

                    // open file
                    $f = @fopen($filename, "w+");

                    // if file can be created, write file, close it and report success, else error
                    if ($f) {
                        fwrite($f,$data);
                        fclose($f);
                        $report_result = "success";
                    } else {
                        $report_result = "error";
                    }
                    print "
                      <li><span class='" . $report_result . "'>" . gTxt('jcr_export_txp_export_'.$report_result) . "</span> " . $config['nice_name'] . " '" . $row['name'] . "' " . gTxt('jcr_export_txp_export_to') . " '" . $nicefilename . "'</li>
                    ";
                }
                print "
                    </ul>
                ";
            }
        }

    }