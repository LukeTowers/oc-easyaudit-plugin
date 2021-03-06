<?php namespace LukeTowers\EasyAudit;

use Backend;
use Backend\Models\User;
use System\Classes\PluginBase;

/**
 * EasyAudit Plugin Information File
 *
 * TODO:
 * - General Facade for generating activity entries
 * - Documentation
 * - Add trackableGetRecordName and trackableGetRecordUrl methods for the activity log to detect the name and URL for a given activity target
 * - Implement templateable descriptions through supporting language strings, some way to provide the attributes and their assigned keys
 *      for being used in the language string as variables. ($activity->description(':event was triggered by :source.full_name'))
 *      To be considered: Import / export of log entries when using language strings. Reevaluate pros/cons of using translateable strings
 *      for the event description in the first place. Perhaps use a system allowing admins to replace event name / description labels for
 *      other users.
 *
 * TODO: Paid version (SystemAuditer or something like that)
 * - Implement ability to enable this plugin's features on other third party plugins that don't actually have support for this plugin built in
 *      Could be very useful, especially the revisionable trait, and even just in general the ability to listen to other plugins and configure auditing for them automatically
 * - Implement ability to have configurable drivers for ouput of the tracking capabilities
 * - Implement Revisionable / trackable properties abilities
 *      Have an option as a model property as to what events to track the revisions on, and then in the backend, you would have the ability to restore revisions (as their own incremental change), but only on the events that revisions are tracked on
 * - Implement configurable rolling log system to dump activity db contents (perhaps even create export jobs for different activity queries),
 *      that will remove db entries past a set date and export them into an importable format and compress them on the disk. Could be part of
 *      a "pro" release
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'luketowers.easyaudit::lang.plugin.name',
            'description' => 'luketowers.easyaudit::lang.plugin.description',
            'author'      => 'LukeTowers',
            'icon'        => 'icon-list-alt',
            'homepage'    => 'https://github.com/LukeTowers/oc-easyaudit-plugin',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'luketowers.easyaudit.manage_settings' => [
                'tab'   => 'luketowers.easyaudit::lang.plugin.name',
                'label' => 'luketowers.easyaudit::lang.permissions.manage_settings'
            ],
            'luketowers.easyaudit.activities.view_all' => [
                'tab'   => 'luketowers.easyaudit::lang.plugin.name',
                'label' => 'luketowers.easyaudit::lang.permissions.activities.view_all'
            ],
            'luketowers.easyaudit.activities.view_own' => [
                'tab'   => 'luketowers.easyaudit::lang.plugin.name',
                'label' => 'luketowers.easyaudit::lang.permissions.activities.view_own'
            ],
        ];
    }

    /**
     * Registers the settings used by this plugin
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'logs' => [
                'label' => 'luketowers.easyaudit::lang.controllers.activities.label',
                'description' => 'luketowers.easyaudit::lang.controllers.activities.description',
                'icon' => 'icon-eye',
                'url' => Backend::url('luketowers/easyaudit/activities'),
                'order' => 1100,
                'permissions' => [
                    'luketowers.easyaudit.activities.*',
                ],
                'category' => \System\Classes\SettingsManager::CATEGORY_LOGS,
            ],
        ];
    }

    /**
     * Register the plugin's form widgets
     *
     * @return array
     */
    public function registerFormWidgets()
    {
        return [
            'LukeTowers\EasyAudit\FormWidgets\ActivityLog' => 'activitylog',
        ];
    }

    /**
     * Register the plugin's report widgets
     *
     * @return array
     */
    public function registerReportWidgets()
    {
        return [
            'LukeTowers\EasyAudit\ReportWidgets\MyActivities' => [
                'label'       => 'luketowers.easyaudit::lang.widgets.myactivities.label',
                'context'     => 'dashboard',
                'permissions' => [
                    'luketowers.easyaudit.activities.view_own'
                ],
            ],
            'LukeTowers\EasyAudit\ReportWidgets\SystemActivities' => [
                'label'       => 'luketowers.easyaudit::lang.widgets.systemactivities.label',
                'context'     => 'dashboard',
                'permissions' => [
                    'luketowers.easyaudit.activities.view_all'
                ],
            ],
        ];
    }

    /**
     * Runs when the plugin is booted
     *
     * @return void
     */
    public function boot()
    {
        User::extend(function ($model) {
            if (empty($model->name)) {
                $model->addDynamicMethod('getNameAttribute', function () use ($model) {
                    return $model->first_name . ' ' . $model->last_name;
                });
            }
        });
    }
}
