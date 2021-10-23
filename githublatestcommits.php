<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use Github\Client;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Symfony\Component\HttpClient\HttplugClient;

class githublatestcommits extends Module implements WidgetInterface
{
    /** @var string */
    private string $templateFile;

    public function __construct()
    {
        $this->name = 'githublatestcommits';
        $this->author = 'Thomas Huynh';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Get github commits');
        $this->description = $this->l('Get last github commits from a public repo');

        $this->templateFile = 'module:githublatestcommits/views/templates/hook/githublatestcommits.tpl';
    }

    /**
     * install pre-config
     *
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (parent::install() &&
            $this->registerHook('displayHome')
        ) {
            Configuration::set('GIT_USER', 'KnpLabs');
            Configuration::set('GIT_REPO', 'php-github-api');
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the installation. Please contact us through Addons website.', [], 'Modules.githublatestcommits.Admin');

        return false;
    }

    /**
     * Uninstall module configuration
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        if (parent::uninstall()) {
            // Configuration
            Configuration::deleteByName('GIT_USER');
            Configuration::deleteByName('GIT_REPO');
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the uninstallation. Please contact us through Addons website.', [], 'Modules.Blockreassurance.Admin');

        return false;
    }


    public function renderForm()
    {
        $fields_options = array(
            'Form' => array(
                'title' => $this->trans('Github lastest commits configuration', [], 'Modules.githublatestcommits.Admin'),
                'icon' => 'icon-cogs',
                'fields' => array(
                    'GITHUB_LATEST_COMMITS_USER' => array(
                        'title' => $this->trans('Github user', [], 'Modules.githublatestcommits.Admin'),
                        'type' => 'text',
                    ),
                    'GITHUB_LATEST_COMMITS_REPO' => array(
                        'title' => $this->trans('Github repo', [], 'Modules.githublatestcommits.Admin'),
                        'type' => 'text',
                    ),
                ),
                'submit' => array('title' => $this->trans('Save', [], 'Modules.githublatestcommits.Admin'))
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGithubLatestCommits';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];

        return $helper->generateForm([$fields_options]);
    }

    /**
     *
     */
    public function getContent() {
        $output = '';
        if (Tools::isSubmit('submitBlockCategories')) {
            $user = Tools::getValue('GITHUB_LATEST_COMMITS_USER');
            $repo = Tools::getValue('GITHUB_LATEST_COMMITS_REPO');

            if (empty($user)||empty($repo)) {
                $output .= $this->displayError($this->getTranslator()->trans('Github User or Repo field are empty.', [], 'Admin.Notifications.Error'));
            } else {
                Configuration::updateValue('GIT_USER', Tools::getValue('GITHUB_LATEST_COMMITS_USER'));
                Configuration::updateValue('GIT_REPO', Tools::getValue('GITHUB_LATEST_COMMITS_REPO'));

                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&conf=6');
            }
        }

        return $output . $this->renderForm();
    }


    public function getConfigFieldsValues()
    {
        return [
            'GITHUB_LATEST_COMMITS_USER' => Tools::getValue('GITHUB_LATEST_COMMITS_USER', Configuration::get('GIT_USER')),
            'GITHUB_LATEST_COMMITS_REPO' => Tools::getValue('GITHUB_LATEST_COMMITS_REPO', Configuration::get('GIT_REPO')),
        ];
    }


    /**
     * @param null $hookName
     * @param array $configuration
     * @return string
     */
    public function renderWidget($hookName = null, array $configuration = []): string
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('githublatestcommits'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('githublatestcommits'));
    }

    /**
     * Get commits from configuration
     * @param $hookName
     * @param array $configuration
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        $gitUser = $configuration['git_user'] ?? null;
        $gitRepo = $configuration['git_repo'] ?? null;

        if ($gitUser && $gitRepo) {
            $user = $gitUser;
            $repo = $gitRepo;
        } else {
            $user = Configuration::get('GIT_USER');
            $repo = Configuration::get('GIT_REPO');
        }

        $client = new \Github\Client();
        // client > endpoints > method/func (commits here)
        if (empty($user) && empty($repo)) {
            $commits = [];
        } else {
            $commits = $client->api('repo')->commits()->all($user, $repo, array('sha' => 'master'));
        }

        return [
            'commits' => $commits
        ];
    }
}
