<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class BlockLanguages extends Module
{
    public function __construct()
    {
        $this->name = 'blocklanguages';
        $this->tab = 'front_office_features';
        $this->version = '1.6.1';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Language selector block');
        $this->description = $this->l('Adds a block allowing customers to select their preferred language for your website\'s content.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('displayNav')
            || !$this->registerHook('displayExternalNavigationHook')
        ) {
            return false;
        }
        return true;
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->addCSS($this->getPathUri().'views/css/hook/blocklanguages.css');
        $this->context->controller->addJS($this->getPathUri().'views/js/hook/blocklanguages.js');
    }

    protected function _prepareHook()
    {
        $languages = Language::getLanguages(true, $this->context->shop->id);
        if (!count($languages)) {
            return false;
        }

        $link = new Link();
        if ((int)Configuration::get('PS_REWRITING_SETTINGS')) {
            $default_rewrite = array();
            if (Dispatcher::getInstance()->getController() == 'product' && ($id_product = (int)Tools::getValue('id_product'))) {
                $rewrite_infos = Product::getUrlRewriteInformations((int)$id_product);
                foreach ($rewrite_infos as $infos) {
                    $default_rewrite[$infos['id_lang']] = $link->getProductLink((int)$id_product, $infos['link_rewrite'], $infos['category_rewrite'], $infos['ean13'], (int)$infos['id_lang']);
                }
            }

            if (Dispatcher::getInstance()->getController() == 'category' && ($id_category = (int)Tools::getValue('id_category'))) {
                $rewrite_infos = Category::getUrlRewriteInformations((int)$id_category);
                foreach ($rewrite_infos as $infos) {
                    $default_rewrite[$infos['id_lang']] = $link->getCategoryLink((int)$id_category, $infos['link_rewrite'], $infos['id_lang']);
                }
            }

            if (Dispatcher::getInstance()->getController() == 'cms' && (($id_cms = (int)Tools::getValue('id_cms')) || ($id_cms_category = (int)Tools::getValue('id_cms_category')))) {
                $rewrite_infos = (isset($id_cms) && !isset($id_cms_category)) ? CMS::getUrlRewriteInformations($id_cms) : CMSCategory::getUrlRewriteInformations($id_cms_category);
                foreach ($rewrite_infos as $infos) {
                    $arr_link = (isset($id_cms) && !isset($id_cms_category)) ?
                        $link->getCMSLink($id_cms, $infos['link_rewrite'], null, $infos['id_lang']) :
                        $link->getCMSCategoryLink($id_cms_category, $infos['link_rewrite'], $infos['id_lang']);
                    $default_rewrite[$infos['id_lang']] = $arr_link;
                }
            }
            $this->smarty->assign('lang_rewrite_urls', $default_rewrite);
        }
        return true;
    }

    public function hookDisplayNav()
    {
        if (!$this->_prepareHook()) {
            return;
        }
        return $this->display(__FILE__, 'blocklanguages.tpl');
    }

    public function hookDisplayTop()
    {
        return $this->hookDisplayNav();
    }

    public function hookDisplayTopSubPrimaryBlock()
    {
        return $this->hookDisplayNav();
    }

    public function hookDisplayFooterMostLeftBlock()
    {
        return $this->hookDisplayNav();
    }

    public function hookDisplayExternalNavigationHook()
    {
        if (!$this->_prepareHook()) {
            return;
        }
        return $this->display(__FILE__, 'external-navigation-hook.tpl');
    }
}
