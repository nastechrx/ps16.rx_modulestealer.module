<?php
if (!defined('_PS_VERSION_'))
{
    exit;
}

class rx_modulesstealer extends Module
{
    public function __construct()
    {
        $this->name = 'rx_modulesstealer';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'rx';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.7.99', ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('El Ladrón de Modulos');
        $this->description = $this->l('Exporta el modulo que selecciones en zip');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('rx_modulesstealer'))
        {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        return parent::install();
    }

    public function getContent()
    {
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name))
        {
            // retrieve the value set by the user
            $configValue = (string)Tools::getValue('module_name');

            $pathdir = dirname(__FILE__) . '/../' . $configValue . '/';
            $zipcreated = $configValue . '_bk1_' . time() . '.zip';

            $this->Zip($pathdir, './'.$zipcreated);



            // $zipcreated = $configValue . '_bk1_' . time() . '.zip';
            // $zip = new ZipArchive();

            // $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source) , RecursiveIteratorIterator::SELF_FIRST);
            // if($zip->open($zipcreated, ZipArchive::CREATE ) === TRUE) {
            //     $dir = opendir($pathdir);
            

            //     while($file = readdir($dir)) {
            //         if(is_file($pathdir.$file)) {
            //             $zip->addFile($pathdir.$file, $configValue.'/'.$file);
            //         }
            //     }
            // }
            // $zip->close();


            if (file_exists($zipcreated))
            {
                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename="' . $zipcreated . '"');
                header('Content-Length: ' . filesize($zipcreated));
                readfile($zipcreated);
                die;
            }

        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    function Zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source))
        {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE))
        {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source) , RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1) , array(
                    '.',
                    '..'
                ))) continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($source . '/', '', $file) , file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true)
        {
            $zip->addFromString(basename($source) , file_get_contents($source));
        }

        return $zip->close();
    }

    public function displayForm()
    {
        // Init Fields form array
        $form = ['form' => ['legend' => ['title' => $this->l('Configuración') , ], 'input' => [['type' => 'text', 'label' => $this->l('nombre de la carpeta del modulo') , 'name' => 'module_name', 'size' => 20, 'required' => true, ], ], 'submit' => ['title' => $this->l('Save') , 'class' => 'btn btn-default pull-right', ], ], ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        return $helper->generateForm([$form]);
    }
}

?>
