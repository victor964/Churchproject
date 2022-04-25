<?php

namespace Give\Tracking\TrackingData;

use Give\License\PremiumAddonsListManager;
use Give\Tracking\Contracts\TrackData;

/**
 * Class ActivePluginsData
 *
 * Represents the plugin data.
 *
 * @package Give\Tracking\TrackingData
 * @since 2.10.0
 */
class PluginsData implements TrackData
{

    /**
     * Returns the collection data.
     *
     * @since 2.10.0
     *
     * @return array The collection data.
     */
    public function get()
    {
        return $this->getPluginData();
    }

    /**
     * Returns all plugins.
     *
     * @since 2.10.0
     *
     * @return array The formatted plugins.
     */
    private function getPluginData()
    {
        $plugins = give_get_plugins();
        $plugins = array_map([$this, 'formatPlugin'], $plugins);

        $plugin_data = [];
        foreach ($plugins as $plugin) {
            $plugin_data[] = $plugin;
        }

        return $plugin_data;
    }

    /**
     * Formats the plugin array.
     *
     * @since 2.10.0
     *
     * @param array $plugin The plugin details.
     *
     * @return array The formatted array.
     */
    private function formatPlugin($plugin)
    {
        $premiumAddonsListManger = give(PremiumAddonsListManager::class);

        return [
            'plugin' => $plugin['Path'],
            'plugin_version' => $plugin['Version'],
            'plugin_status' => $plugin['Status'],
            'plugin_type' => $plugin['Type'],
            'is_premium' => absint($premiumAddonsListManger->isPremiumAddons($plugin['PluginURI'])),
        ];
    }
}

