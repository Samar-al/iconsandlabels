<?php
/**
 * IconsAndLabelsAddClass.php
 *
 *
 *
 * @author    Samar Al Khalil
 * @copyright Copyright (c)
 * @license   License (if applicable)
 * @category  Classes
 * @package   Icons&Labels
 * @subpackage Classes
 */
class IconsAndLabelsAddClass extends ObjectModel
{
    public $id_icons_labels;
    public $title;
    public $description;
    public $image;
    public $lang_code;
    public $position;
    public $product;

    /**
      * Custom method to handle multilingual fields
      *
      * @param int $id_icons_labels
      * @param int|null $id_lang
      * @param int|null $id_shop
      */
    public function __construct($id_icons_labels = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_icons_labels, $id_lang, $id_shop);

        if ($id_lang !== null && $id_lang !== Context::getContext()->language->id) {
            $this->title = null;
            $this->description = null;
        }
    }

    public static $definition = [
        'table' => 'icons_labels',
        'primary' => 'id_icons_labels',
        'multilang' => true,
        'fields' => [
            'title' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true, 'lang'=>true],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true, 'lang'=>true],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'lang_code' => ['type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt', 'required' => true],
            'position' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        if (!$this->position) {
            $this->position = 'top-left'; // Set a default position if not provided
        }
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        if (!$this->position) {
            $this->position = 'top-left'; // Set a default position if not provided
        }
        return parent::update($null_values);
    }

}
