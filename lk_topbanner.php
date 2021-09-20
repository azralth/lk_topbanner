<?php
/**
 *  Copyright (C) Lk Interactive - All Rights Reserved.
 *
 *  This is proprietary software therefore it cannot be distributed or reselled.
 *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  Proprietary and confidential.
 *
 * @author    Lk Interactive <contact@lk-interactive.fr>
 * @copyright 2020.
 * @license   Commercial license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Lk_topbanner extends Module
{
    private $templateFile;

    /**
     * Lk_Neonegoce constructor.
     */
    public function __construct()
    {
        $this->name = 'lk_topbanner';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Lk Interactive';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7.6.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->templateFile = 'module:lk_topbanner/views/front/hook/lk_topbanner.tpl';

        parent::__construct();

        $this->displayName = $this->trans('Lk Interactive - Top banner.', array(), 'Modules.Lktopbanner.Admin');
        $this->description = $this->trans('Add highlight information and social a top in your shop', array(), 'Modules.Lktopbanner.Admin');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall ?', 'Lktopbanner');
    }

    /**
     * @return mixed
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayBanner')
            && $this->installMixtures();
    }


    /**
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function installMixtures()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            Configuration::updateValue('LK_TOPBANNER_TEXT_'.$lang['id_lang'].'', 'your text here');
            Configuration::updateValue('LK_TOPBANNER_FACEBOOK_'.$lang['id_lang'].'', '#');
            Configuration::updateValue('LK_TOPBANNER_PINTEREST_'.$lang['id_lang'].'', '#');
            Configuration::updateValue('LK_TOPBANNER_INSTAGRAM_'.$lang['id_lang'].'', '#');
        }

        return true;
    }

    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitLkTopBanner')) == true) {
            $this->postProcess();
        }

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLkTopBanner';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Top Banner info'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Text banner', array(), 'Modules.Lktopbanner.Admin'),
                        'name' => 'LK_TOPBANNER_TEXT',
                        'cols' => 40,
                        'rows' => 10,
                        'autoload_rte' => true,
                        'class' => 'rte',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Facebook url', array(), 'Modules.Lktopbanner.Admin'),
                        'name' => 'LK_TOPBANNER_FACEBOOK',
                        'lang' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Pinterest url', array(), 'Modules.Lktopbanner.Admin'),
                        'name' => 'LK_TOPBANNER_PINTEREST',
                        'lang' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Instagram url', array(), 'Modules.Lktopbanner.Admin'),
                        'name' => 'LK_TOPBANNER_INSTAGRAM',
                        'lang' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    protected function getConfigFormValues()
    {
        $languages = Language::getLanguages(false);
        $fields = array();

        foreach ($languages as $lang) {
            $fields['LK_TOPBANNER_TEXT'][$lang['id_lang']] = Tools::getValue('LK_TOPBANNER_TEXT_'.$lang['id_lang'], Configuration::get('LK_TOPBANNER_TEXT', $lang['id_lang']));
            $fields['LK_TOPBANNER_FACEBOOK'][$lang['id_lang']] = Tools::getValue('LK_TOPBANNER_FACEBOOK_'.$lang['id_lang'], Configuration::get('LK_TOPBANNER_FACEBOOK', $lang['id_lang']));
            $fields['LK_TOPBANNER_INSTAGRAM'][$lang['id_lang']] = Tools::getValue('LK_TOPBANNER_INSTAGRAM_'.$lang['id_lang'], Configuration::get('LK_TOPBANNER_INSTAGRAM', $lang['id_lang']));
            $fields['LK_TOPBANNER_PINTEREST'][$lang['id_lang']] = Tools::getValue('LK_TOPBANNER_PINTEREST_'.$lang['id_lang'], Configuration::get('LK_TOPBANNER_PINTEREST', $lang['id_lang']));
        }

        return $fields;
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitLkTopBanner')) {
            $languages = Language::getLanguages(false);
            $values = array();

            foreach ($languages as $lang) {
                $values['LK_TOPBANNER_TEXT'][$lang['id_lang']] =  Tools::getValue('LK_TOPBANNER_TEXT_'.$lang['id_lang']);
                $values['LK_TOPBANNER_FACEBOOK'][$lang['id_lang']] =  Tools::getValue('LK_TOPBANNER_FACEBOOK_'.$lang['id_lang']);
                $values['LK_TOPBANNER_PINTEREST'][$lang['id_lang']] =  Tools::getValue('LK_TOPBANNER_PINTEREST_'.$lang['id_lang']);
                $values['LK_TOPBANNER_INSTAGRAM'][$lang['id_lang']] =  Tools::getValue('LK_TOPBANNER_INSTAGRAM_'.$lang['id_lang']);
            }
            Configuration::updateValue('LK_TOPBANNER_TEXT', $values['LK_TOPBANNER_TEXT']);
            Configuration::updateValue('LK_TOPBANNER_FACEBOOK', $values['LK_TOPBANNER_FACEBOOK']);
            Configuration::updateValue('LK_TOPBANNER_PINTEREST', $values['LK_TOPBANNER_PINTEREST']);
            Configuration::updateValue('LK_TOPBANNER_INSTAGRAM', $values['LK_TOPBANNER_INSTAGRAM']);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        return '';
    }

    public function hookHeader()
    {
        $this->context->controller->registerStylesheet('lk-topbanner-css', 'modules/'.$this->name.'/assets/css/lk_topbanner.css');
    }

    public function hookDisplayBanner()
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('lk_topbanner'))) {
            $id_lang = $this->context->language->id;
            $fields['top_banner_text'] = Configuration::get('LK_TOPBANNER_TEXT_'.$id_lang);
            $fields['facebook'] = Configuration::get('LK_TOPBANNER_FACEBOOK_'.$id_lang);
            $fields['instagram'] = Configuration::get('LK_TOPBANNER_INSTAGRAM_'.$id_lang);
            $fields['pinterest'] = Configuration::get('LK_TOPBANNER_PINTEREST_'.$id_lang);

            $this->context->smarty->assign([
                'lk_topbanner_text' => $fields['top_banner_text'],
                'lk_topbanner_social' => $fields,
            ]);
        }
        return $this->fetch($this->templateFile, $this->getCacheId('lk_topbanner'));
    }
}