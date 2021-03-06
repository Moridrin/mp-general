<?php

namespace mp_general\forms;

use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;
use mp_general\forms\models\Form;
use mp_general\forms\models\FormField;
use mp_general\forms\models\SharedField;
use mp_general\forms\models\SiteSpecificField;

if (!defined('ABSPATH')) {
    exit;
}

/** @noinspection PhpIncludeInspection */
require_once SSV_Forms::PATH . 'templates/form-editor.php';

abstract class SSV_Forms
{
    const PATH = SSV_FORMS_PATH;
    const URL  = SSV_FORMS_URL;

    const ADMIN_REFERER = 'ssv_forms__admin_referer';

    public static function addSite(int $blogId)
    {
        foreach (wp_get_active_network_plugins() as $plugin) {
            if (preg_match_all('/.*(ssv[a-z-]+).php/', $plugin)) {
                self::setupForBlog($blogId);
                return;
            }
        }
    }

    public static function setupForBlog(int $blogId = null)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        $wpdb->query(SharedField::getDatabaseCreateQuery($blogId));
        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
        $wpdb->query(SiteSpecificField::getDatabaseCreateQuery($blogId));
        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
        $wpdb->query(FormField::getDatabaseCreateQuery($blogId));
        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
        $wpdb->query(Form::getDatabaseCreateQuery($blogId));
        if ($wpdb->last_error) {
            throw new \Exception($wpdb->last_error);
        }
    }

    public static function deleteSite(int $blogId)
    {
        self::cleanupBlog($blogId);
    }

    public static function cleanupBlog(int $blogId = null)
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tableName = SiteSpecificField::getDatabaseTableName($blogId);
        $wpdb->query("DROP TABLE $tableName;");
        if ($wpdb->last_error) {
            // throw new \Exception($wpdb->last_error);
        }
        $tableName = FormField::getDatabaseTableName($blogId);
        $wpdb->query("DROP TABLE $tableName;");
        if ($wpdb->last_error) {
            // throw new \Exception($wpdb->last_error);
        }
        $tableName = Form::getDatabaseTableName($blogId);
        $wpdb->query("DROP TABLE $tableName;");
        if ($wpdb->last_error) {
            // throw new \Exception($wpdb->last_error);
        }
    }

    public static function setup($network_wide)
    {
        if (is_multisite() && $network_wide) {
            SSV_Global::runFunctionOnAllSites([self::class, 'setupForBlog']);
        } else {
            self::setupForBlog();
        }
    }

    public static function deactivate($network_wide)
    {
        if (is_multisite() && $network_wide) {
            //            SSV_Global::runFunctionOnAllSites([self::class, 'cleanupBlog']); // Don't remove the databases on a netword disable (to keep the data for when a blog still wants to use the data).
        } else {
            // Check if this is the last SSV plugin to be deactivated.
            self::cleanupBlog();
        }
    }

    public static function enqueueAdminScripts()
    {
        $page = $_GET['page'] ?? null;
        if (!in_array($page, ['ssv_forms_fields_manager', 'ssv_forms', 'ssv_forms_add_new_form'])) {
            return;
        }
        switch ($page) {
            case 'ssv_forms_fields_manager':
                self::enquireFieldsManagerScripts();
                break;
            case 'ssv_forms':
                if (is_network_admin()) {
                    self::enquireFieldsManagerScripts();
                } else {
                    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                        self::enquireFormEditorScripts();
                        self::enquireFieldsManagerScripts();
                    } else {
                        self::enquireFormsManagerScripts();
                    }
                }
                break;
            case 'ssv_forms_add_new_form':
                self::enquireFormEditorScripts();
                self::enquireFieldsManagerScripts();
                break;
        }
    }

    private static function enquireFieldsManagerScripts()
    {
        $activeTab = $_GET['tab'] ?? 'shared';
        wp_enqueue_script('mp-ssv-fields-manager', SSV_Forms::URL . '/js/fields-manager.js', ['jquery']);
        wp_localize_script('mp-ssv-fields-manager', 'mp_ssv_fields_manager_params', [
            'urls'       => [
                'plugins'  => plugins_url(),
                'ajax'     => admin_url('admin-ajax.php'),
                'base'     => get_home_url(),
                'basePath' => ABSPATH,
            ],
            'actions'    => [
                'save'   => 'mp_general_forms_save_field',
                'delete' => 'mp_general_forms_delete_field',
            ],
            'isShared'   => $activeTab === 'shared',
            'roles'      => array_keys(get_editable_roles()),
            'inputTypes' => BaseFunctions::getInputTypes($activeTab === 'shared' ? ['role_checkbox', 'role_select'] : []),
            'formId'     => $_GET['id'] ?? null,
        ]);
    }

    private static function enquireFormEditorScripts()
    {
        wp_enqueue_script('mp-ssv-form-editor', SSV_Forms::URL . '/js/form-editor.js', ['jquery']);
    }

    private static function enquireFormsManagerScripts()
    {
        wp_enqueue_script('mp-ssv-forms-manager', SSV_Forms::URL . '/js/forms-manager.js', ['jquery']);
        wp_localize_script('mp-ssv-forms-manager', 'mp_ssv_forms_manager_params', [
            'urls'    => [
                'plugins'  => plugins_url(),
                'ajax'     => admin_url('admin-ajax.php'),
                'base'     => get_home_url(),
                'basePath' => ABSPATH,
            ],
            'actions' => [
                'delete' => 'mp_general_forms_delete_form',
            ],
            'formId'  => $_GET['id'] ?? null,
        ]);
    }

    public static function filterContent($content)
    {
        //        $database = SSV_Global::getDatabase();
        //        $table    = SSV_Forms::SITE_SPECIFIC_FORMS_TABLE;
        //        $forms    = $database->get_results("SELECT * FROM $table");
        //        foreach ($forms as $form) {
        //            if (strpos($content, $form->f_tag) !== false) {
        //                $content = str_replace($form->f_tag, self::getFormFieldsHTML($form->f_id), $content);
        //            }
        //        }
        return $content;
    }

    /**
     * @param $networkEnable
     * @throws \Exception
     */
    public static function CLEAN_INSTALL($networkEnable)
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tableName = SharedField::getDatabaseTableName();
        $wpdb->query("DROP TABLE $tableName;");
        if ($networkEnable) {
            SSV_Global::runFunctionOnAllSites([self::class, 'cleanInstallBlog']);
        } else {
            self::cleanInstallBlog();
        }
    }

    /**
     * @throws \Exception
     */
    public static function cleanInstallBlog()
    {
        self::cleanupBlog();
        self::setupForBlog();
    }

    public static function filterForms(array $attributes)
    {
        $form = Form::findById($attributes['id']);
        echo (string)$form;
    }
}

register_activation_hook(SSV_FORMS_ACTIVATOR_PLUGIN, [SSV_Forms::class, 'setup']);
register_deactivation_hook(SSV_FORMS_ACTIVATOR_PLUGIN, [SSV_Forms::class, 'deactivate']);
add_action('wpmu_new_blog', [SSV_Forms::class, 'addSite']);
add_action('delete_blog', [SSV_Forms::class, 'deleteSite']);
add_action('admin_enqueue_scripts', [SSV_Forms::class, 'enqueueAdminScripts']);
add_filter('the_content', [SSV_Forms::class, 'filterContent']);
add_shortcode('ssv-form', [SSV_Forms::class, 'filterForms']);
