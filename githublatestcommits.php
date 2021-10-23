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
        $this->tab = 'front_office_features';
        $this->ps_versions_compliancy = ['min' => '1.7.4.0', 'max' => _PS_VERSION_];
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
        if (parent::install() &&
            $this->registerHook('displayHome')
        ) {
            Configuration::set('GIT_USER', 'KnpLabs');
            Configuration::set('GIT_REPO', 'php-github-api');
            Configuration::set('GIT_NUMBER', 5);
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
            Configuration::deleteByName('GIT_NUMBER');
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the uninstallation. Please contact us through Addons website.', [], 'Modules.Blockreassurance.Admin');

        return false;
    }


    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Github lastest commits configuration', [], 'Modules.githublatestcommits.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Github user', [], 'Modules.githublatestcommits.Admin'),
                        'name' => 'GITHUB_LATEST_COMMITS_USER',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Github repo', [], 'Modules.githublatestcommits.Admin'),
                        'name' => 'GITHUB_LATEST_COMMITS_REPO',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Number of commits (default value if empty : 5)', [], 'Modules.githublatestcommits.Admin'),
                        'name' => 'GITHUB_COMMITS_NUMBER',
                    ],
                ],
                'submit' => [
                    'title' => $this->getTranslator()->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitGithubLatestCommits';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     *
     */
    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitGithubLatestCommits')) {
            Configuration::updateValue('GIT_USER', Tools::getValue('GITHUB_LATEST_COMMITS_USER'));
            Configuration::updateValue('GIT_REPO', Tools::getValue('GITHUB_LATEST_COMMITS_REPO'));
            Configuration::updateValue('GIT_NUMBER', (int) Tools::getValue('GITHUB_COMMITS_NUMBER'));

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&conf=6');
        }

        return $output . $this->renderForm();
    }


    public function getConfigFieldsValues()
    {
        return [
            'GITHUB_LATEST_COMMITS_USER' => Tools::getValue('GITHUB_LATEST_COMMITS_USER', Configuration::get('GIT_USER')),
            'GITHUB_LATEST_COMMITS_REPO' => Tools::getValue('GITHUB_LATEST_COMMITS_REPO', Configuration::get('GIT_REPO')),
            'GITHUB_COMMITS_NUMBER' => Tools::getValue('GITHUB_COMMITS_NUMBER', Configuration::get('GIT_NUMBER')),
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
     * @param $hookName
     * @param array $configuration
     * @return array
     */
    public function getWidgetVariables($hookName, array $configuration): array
    {
        $gitUser = $configuration['git_user'] ?? null;
        $gitRepo = $configuration['git_repo'] ?? null;
        $numberOfCommits = (int) Configuration::get('GIT_NUMBER') ?? 5;

        if ($gitUser && $gitRepo) {
            $user = $gitUser;
            $repo = $gitRepo;
        } else {
            $user = Configuration::get('GIT_USER');
            $repo = Configuration::get('GIT_REPO');
        }

        return [
            'commits' => $this->getCommits($user, $repo, $numberOfCommits),
            'user' => $user,
            'repo' => $repo,
            'number' => $numberOfCommits
        ];
    }

    public function hookDisplayHome()
    {
        $this->context->controller->registerStylesheet(
            'githublatestcommits-style',
            'modules/'.$this->name.'/views/css/githublatestcommits.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );

        $this->context->controller->registerStylesheet(
            'bootstrap',
            'modules/'.$this->name.'/views/css/bootstrap.min.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );

        return $this->renderWidget();
    }

    /**
     * Get commits from configuration
     * @param $user
     * @param $repo
     * @param $numberOfCommits
     * @return array
     */
    public function getCommits($user, $repo, $numberOfCommits): array
    {
        $client = new \Github\Client();

        if (empty($user) && empty($repo)) {
            $commits = [];
        } else {
            try {
                // client > endpoints > method/func (commits here)
                $commits = $client->api('repo')->commits()->all($user, $repo, array('sha' => 'master'));
            } catch (Exception $e) {
                $commits = ['error' => 'error'];
            }
        }

        return array_slice($commits, 0, $numberOfCommits, true);
    }
}
