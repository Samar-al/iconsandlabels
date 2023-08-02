<?php
/**
 * AdminIconsAndLabelsAddController.php
 *  @author    Samar Al khalil
 *  @copyright Copyright (c) 2023
 *  @license   License (if applicable)
 *  @category  Controllers
 *
 */
require_once(_PS_MODULE_DIR_.'iconsandlabels/classes/IconsAndLabelsAddClass.php');

class AdminIconsAndLabelsAddController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = IconsAndLabelsAddClass::$definition['table'];
        $this->className = IconsAndLabelsAddClass::class;
        $this->module = Module::getInstanceByName('iconsandlabels');
        $this->identifier = IconsAndLabelsAddClass::$definition['primary'];
        $this->_orderBy = IconsAndLabelsAddClass::$definition['primary'];
        $this->lang = true;
        $this->allow_export = true;
        $this->context = Context::getContext();


        parent::__construct();

        $this->fields_list = [
            'id_icons_labels' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'title' => [
                'title' => $this->l('Titre'),
                'filter_key' => 'a!title',

            ],
            'description' => [
                'title' => $this->l('Description'),
                'filter_key' => 'ill!description',
                'callback' => 'stripTagsFromDescription',
            ],
            'image' => [
                'title' => $this->l('Image'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'lang_code' => [
                'title' => $this->l('Language'),
                'filter_key' => 'a!language',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'callback' => 'getLanguageName',
            ],
            'position' => [
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'callback' => 'formatPosition',
            ],
            'product' => [
                'title' => $this->l('Product ID and Name'),
                'filter_key' => 'p!name',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'callback' => 'getProductNameAndId',
            ],
        ];



        $this->addRowAction('edit');
        $this->addRowAction('delete');

    }

    public function getLanguageName()
    {
        $language = Language::getLanguages(false);
        if ($language && isset($language[0]['name'])) {
            return $language[0]['name'];
        }

        return '-';
    }

    public function stripTagsFromDescription($description)
    {
        return strip_tags($description);
    }

    public function formatPosition($position)
    {
        $positions = [
            'top: 0; left: 0;' => 'Top - Left',
            'top: 0; right: 0;' => 'Top - Right',
            'bottom: 0; left: 2px;' => 'Bottom - Left',
            'bottom: 0; right: 0;' => 'Bottom - Right',
        ];

        return isset($positions[$position]) ? $positions[$position] : '-';
    }

    public function renderForm()
    {
        $languages = Language::getLanguages(false);
        $languageOptions = [];
        foreach ($languages as $language) {
            $languageOptions[] = [
                'id_option' => $language['id_lang'],
                'name' => $language['name'],
            ];
        }

        $positionOptions = [
            ['id_option' => 'top: 0; left: 0;', 'name' => 'Top - Left'],
            ['id_option' => 'top: 0; right: 0;', 'name' => 'Top - Right'],
            ['id_option' => 'bottom: 0; left: 2px;', 'name' => 'Bottom - Left'],
            ['id_option' => 'bottom: 0; right: 0;', 'name' => 'Bottom - Right'],
        ];

        $products = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');
        $productOptions = [];
        foreach ($products as $product) {
            $productOptions[] = [
                'id_option' => $product['id_product'],
                'name' => $product['name'],
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Add an Icons or a Label'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Titre'),
                    'name' => 'title',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'required' => true,
                    'lang' => true,
                    'max_length' => 100,
                    'autoload_rte' => true,
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'image',
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Language'),
                    'name' => 'lang_code',
                    'required' => true,
                    'options' => [
                        'query' => $languageOptions,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Position'),
                    'name' => 'position',
                    'required' => true,
                    'options' => [
                        'query' => $positionOptions,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Product'),
                    'name' => 'product',
                    'required' => true,
                    'options' => [
                        'query' => $productOptions,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'name' => 'submit',
                'class' => 'btn btn-warning',
            ],
            'form' => [
                'enctype' => 'multipart/form-data', // Set enctype to enable file uploads
            ],
        ];

        return parent::renderForm();
    }

    public function getProductNameAndId($productId, $row)
    {
        $product = new Product($productId, false, Context::getContext()->language->id);
        if (Validate::isLoadedObject($product)) {
            if (isset($product->name) && !empty($product->name)) {
                return $productId . ' - ' . $product->name;
            }
        }

        return '-';
    }

    public function postProcess()
    {
        if (Tools::isSubmit('delete'.$this->table)) {
            $id = (int) Tools::getValue($this->identifier);
            if ($id > 0) {
                // Load the object to be deleted
                $iconsAndLabels = new IconsAndLabelsAddClass($id);
                if (Validate::isLoadedObject($iconsAndLabels)) {
                    // Delete the object
                    $iconsAndLabels->delete();
                    // Redirect to the list page after deletion
                    $this->redirect_after = self::$currentIndex.'&conf=1&token='.$this->token;
                } else {
                    // Object not found or cannot be deleted
                    $this->errors[] = $this->l('Unable to delete the record.');
                }
            } else {
                // Invalid or missing ID
                $this->errors[] = $this->l('Invalid record ID.');
            }
        } elseif (Tools::isSubmit('submit')) {
            $hasNewImage = $this->processImageUpload();

            // Save reassurance item to the database
            if ($this->className && ($id = (int) Tools::getValue($this->identifier))) {
                $icons = new $this->className($id);
                if (Validate::isLoadedObject($icons)) {
                    // Check if the submitted image is empty
                    $submittedImage = Tools::getValue('image');
                    if (!$hasNewImage && empty($submittedImage)) {
                        // If no new image is provided, keep the existing image in the database
                        $_POST['image'] = $icons->image;
                    } elseif ($hasNewImage) {
                        // If a new image is uploaded, we need to remove the old one if it exists
                        if (!empty($icons->image)) {
                            $this->removeOldImage($icons->image);
                        }
                    }

                    $this->copyFromPost($icons, $this->table);
                    $icons->update();
                }
            } else {
                $icons = new IconsAndLabelsAddClass();
                // Check if a new image is uploaded
                if (!$hasNewImage) {
                    // If no new image is provided, set the image to the existing image in the database
                    $icons->image = Tools::getValue('image');
                }

                $this->copyFromPost($icons, $this->table);
                $icons->add();
            }

            // Redirect to the list page after saving
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
        }
    }

    protected function removeOldImage($imageName)
    {
        $imagePath = _PS_MODULE_DIR_ . 'iconsandlabels/views/img/images/' . $imageName;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    protected function processImageUpload()
    {
        $uploadDir = _PS_MODULE_DIR_ . 'iconsandlabels/views/img/images/';
        $fileName = 'icons_and_labels_' . md5(uniqid()) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $targetFile = $uploadDir . $fileName;

        $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp');
        if (!in_array(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION), $allowedExtensions)) {
            $this->errors[] = $this->l('Invalid file format. Allowed formats are jpg, jpeg, and png.');
            return false;
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            return false;
        }

        $_POST['image'] = $fileName;
        return true;
    }

}
