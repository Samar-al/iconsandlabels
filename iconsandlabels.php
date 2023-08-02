<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if(!defined('_PS_VERSION_')) {
    exit;
}

class IconsAndLabels extends Module
{
    public function __construct()
    {
        $this->name ='iconsandlabels';

        $this->tab = 'front_office_features';

        $this->version = '1.0.0';

        $this->author = 'samar Al khalil';

        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];

        parent::__construct();

        $this->bootstrap = true;

        $this->displayName = $this->l('Icons & Labels');

        $this->description = $this->l('Charger afficher des icons et label sur les produits');

        $this->confirmUninstall = $this->l('Êtes-vous sur de vouloir supprimer ce module');
    }

    public function install()
    {
        if (!parent::install() ||

            !$this->registerHook('displayReassurance') ||
            !$this->registerHook('actionProductFlagsModifier') ||
            !$this->registerHook('displayProductAdditionalInfo') ||
            !$this->createTable() ||
            !$this->installTab('AdminIconsAndLabelsAdd', 'Add Icons/Labels', 'IMPROVE')

        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||

            !$this->unregisterHook('displayReassurance') ||
            !$this->unregisterHook('actionProductFlagsModifier') ||
            !$this->unregisterHook('displayProductAdditionalInfo') ||
            !$this->removeTable() ||
            !$this->uninstallTab('AdminIconsAndLabelsAdd')
        ) {
            return false;
        }

        return true;
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all)
            && $this->installTab('AdminIconsAndLabelsAdd', 'Add Icons/Labels', 'IMPROVE');

    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTab('AdminIconsAndLabelsAdd');


    }

    public function getContent()
    {

    }

    public function createTable()
    {
        $sql1 = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'icons_labels` (
            `id_icons_labels` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `image` VARCHAR(255) NOT NULL,
            `position` VARCHAR(50) NOT NULL,
            `product` INT UNSIGNED NOT NULL,
            `lang_code` VARCHAR(10) NOT NULL,
            PRIMARY KEY (`id_icons_labels`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        $sql2 = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'icons_labels_lang` (
            `id_icons_labels` INT UNSIGNED NOT NULL,
            `id_lang` INT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            PRIMARY KEY (`id_icons_labels`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        return Db::getInstance()->execute($sql1) && Db::getInstance()->execute($sql2);


    }

    public function removeTable()
    {
        $sql1 = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'icons_labels`';
        $sql2 = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'icons_labels_lang`';

        return Db::getInstance()->execute($sql1) && Db::getInstance()->execute($sql2);
    }



    private function installTab($className, $tabName, $tabParentName)
    {
        $tabId = (int) Tab::getIdFromClassName($className);
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = $className;
        // Only since 1.7.7, you can define a route name
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans($tabName, array(), 'Modules.MyModule.Admin', $lang['locale']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab($className)
    {
        $tabId = (int) Tab::getIdFromClassName($className);
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    public function getProductIdsWithIcons()
    {
        $productIdsWithIcons = [];
        $sql = 'SELECT il.product, il.image, il.position, ill.title, ill.description 
                FROM `' . _DB_PREFIX_ . 'icons_labels` il
                LEFT JOIN `' . _DB_PREFIX_ . 'icons_labels_lang` ill ON il.`id_icons_labels` = ill.`id_icons_labels` AND ill.`id_lang` = ' . (int)$this->context->language->id;

        $result = Db::getInstance()->executeS($sql);

        if ($result && is_array($result)) {
            foreach ($result as $row) {

                $productId = (int) $row['product'];
                $iconImageUrl = $this->_path . 'views/img/images/' . $row['image'];
                $position = $row['position'];
                $title = $row['title'];
                $description = strip_tags($row['description']);

                $productIdsWithIcons[$productId] = [
                    'icon' => $iconImageUrl,
                    'position' => $position,
                    'productId' => $productId,
                    'title' => $title,
                    'description' => $description,
                ];
            }
        }

        return $productIdsWithIcons;
    }



    public function hookActionProductFlagsModifier($params)
    {

        $productIdsWithIcons = $this->getProductIdsWithIcons();
        $product = $params['product'];
        $productId = $product['id_product'];

        if (isset($productIdsWithIcons[$productId])) {
            $iconUrl = 'http://' . $_SERVER['HTTP_HOST'] . $productIdsWithIcons[$productId]['icon'];
            $position = $productIdsWithIcons[$productId]['position'];
            // Add the icon to the product image
            echo '<div class="product-icon" style="position: relative;">';
            echo '<img src="' . $iconUrl . '" alt="Icon" style="width: 50px; position: absolute; '. $position .'">';
            echo '</div>';
            echo '<script>';
            echo 'document.addEventListener("DOMContentLoaded", function() {';
            echo 'const productCover = document.querySelector(".product-cover");';
            echo 'const productIcon = document.querySelector(".product-icon");';
            echo 'const thumbnailImg = document.querySelector(".thumbnail.product-thumbnail img");';
            echo 'const width = productCover ? productCover.offsetWidth : thumbnailImg.offsetWidth;';
            echo 'const height = productCover ? productCover.offsetHeight : thumbnailImg.offsetHeight;';
            echo 'productIcon.style.width = width + "px";';
            echo 'productIcon.style.height = height + "px";';
            echo '});';
            echo '</script>';
        }
    }


    public function hookDisplayReassurance($params)
    {
        $productIdsWithIcons = $this->getProductIdsWithIcons();
        $productId =(int)Tools::getValue('id_product');

        if (isset($productIdsWithIcons[$productId])) {
            $iconUrl = 'http://' . $_SERVER['HTTP_HOST'] . $productIdsWithIcons[$productId]['icon'];
            $position = $productIdsWithIcons[$productId]['position'];
            $title = $productIdsWithIcons[$productId]['title'];
            $description = $productIdsWithIcons[$productId]['description'];

            $this->smarty->assign(array(
                'iconUrl' => $iconUrl,
                'position' => $position,
                'title' => $title,
                'description' => $description,
            ));

            return $this->display(__FILE__, 'icons_labels.tpl');
        }
    }

    public function hookDisplayProductAdditionalInfo()
    {
        $productIdsWithIcons = $this->getProductIdsWithIcons();
        $productId =(int)Tools::getValue('id_product');

        if (isset($productIdsWithIcons[$productId])) {
            $iconUrl = 'http://' . $_SERVER['HTTP_HOST'] . $productIdsWithIcons[$productId]['icon'];
            $position = $productIdsWithIcons[$productId]['position'];
            $title = $productIdsWithIcons[$productId]['title'];
            $description = $productIdsWithIcons[$productId]['description'];

            $this->smarty->assign(array(
                'iconUrl' => $iconUrl,
                'position' => $position,
                'title' => $title,
                'description' => $description,
            ));

            return $this->display(__FILE__, 'icons_labels.tpl');
        }
    }
}
