<?php
if (!defined('_PS_VERSION_'))
  exit;

class wwpopup extends Module{
  public function __construct(){
    $this->name = 'wwpopup';
    $this->tab = 'front_office_features';
    $this->version = '1.1.2';
    $this->author = 'Web-Wave';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('WW Popup');
    $this->description = $this->l('A custom module for add a popup on the homepage.');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall WW Popup?');
  }

	public function install(){
	  if (Shop::isFeatureActive())
	    Shop::setContext(Shop::CONTEXT_ALL);

    if (!parent::install() ||
      !$this->registerHook('header') ||
      !Configuration::updateValue('POPUP_TITLE', 'Title') ||
      !Configuration::updateValue('POPUP_BODY', 'Content')
    )
      return false;

	  return true;
	}

	public function uninstall(){
  	return parent::uninstall() && $this->uninstallDB();
  }

	public function uninstallDB(){
		$ret = true;
		$ret &=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'info`') && Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'info_lang`');
		return $ret;
	}

  public function getContent(){
    $output = null;

    if (Tools::isSubmit('submit'.$this->name)){
      foreach (Language::getLanguages(false) as $lang){
        $helper->languages[] = array(
          'id_lang' => $lang['id_lang'],
          'iso_code' => $lang['iso_code'],
          'name' => $lang['name'],
          'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
         );
         Configuration::updateValue('POPUP_TITLE_'.$lang['id_lang'], Tools::getValue('POPUP_TITLE_'.$lang['id_lang']));
         Configuration::updateValue('POPUP_BODY_'.$lang['id_lang'], Tools::getValue('POPUP_BODY_'.$lang['id_lang']), true);
      }
      $this->_clearCache('display.tpl');
      $output .= $this->displayConfirmation($this->l('Settings updated for WW Popup.'));
    }
    return $output.$this->displayForm();
  }

  public function displayForm(){
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $fields_form[0]['form'] = array(
        'tinymce' => true,
        'legend' => array(
            'title' => $this->l('Settings WW Popup'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Title'),
                'name' => 'POPUP_TITLE',
                'size' => 20,
                'required' => false,
                'lang' => true
            ),
            array(
              'type' => 'textarea',
    					'label' => $this->l('Body'),
    					'name' => 'POPUP_BODY',
    					'cols' => 40,
    					'rows' => 5,
    					'class' => 'rte',
    					'autoload_rte' => true,
              'lang' => true
  					),
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        )
    );

    $helper = new HelperForm();
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->identifier = $this->identifier;
    foreach (Language::getLanguages(false) as $lang)
  		$helper->languages[] = array(
  			'id_lang' => $lang['id_lang'],
  			'iso_code' => $lang['iso_code'],
  			'name' => $lang['name'],
  			'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
  	  );

    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;
    $helper->toolbar_scroll = true;
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
          'desc' => $this->l('Save'),
          'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
          '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
          'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
          'desc' => $this->l('Back to list')
        )
    );

    foreach (Language::getLanguages(false) as $lang){
      $helper->fields_value['POPUP_TITLE'][(int)$lang['id_lang']] = Configuration::get('POPUP_TITLE_'.(int)$lang['id_lang'], '');
      $helper->fields_value['POPUP_BODY'][(int)$lang['id_lang']] = Configuration::get('POPUP_BODY_'.(int)$lang['id_lang'], '');
    }

    return $helper->generateForm($fields_form);
  }

  public function hookDisplayHeader($params){
    foreach (Language::getLanguages(false) as $lang){
      $helper->languages[] = array(
        'id_lang' => $lang['id_lang'],
        'iso_code' => $lang['iso_code'],
        'name' => $lang['name'],
        'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
       );
    }

    $this->context->smarty->assign(
      array(
        'popup_title' => Configuration::get('POPUP_TITLE_'.$this->context->language->id),
        'popup_body' => Configuration::get('POPUP_BODY_'.$this->context->language->id),
        'popup_link' => $this->context->link->getModuleLink('wwpopup', 'display')
      )
    );

    $this->context->controller->addCSS($this->_path.'css/wwpopup.css', 'all');
    $this->context->controller->addJS($this->_path.'js/wwpopup.js', 'all');
    return $this->display(__FILE__, 'display.tpl');
  }
}
