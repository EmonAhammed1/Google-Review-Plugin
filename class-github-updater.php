<?php
if (!defined('ABSPATH')) exit;

class Google_Review_Emon_GitHub_Updater {

    private $file;
    private $plugin;
    private $slug;
    private $username;
    private $repository;
    private $version;
    private $github_response;

    public function __construct($file, $username, $repository, $version) {
        $this->file = $file;
        $this->plugin = plugin_basename($file);
        $this->slug = dirname($this->plugin);
        $this->username = $username;
        $this->repository = $repository;
        $this->version = $version;

        if (isset($_GET['force-check'])) {
            delete_site_transient('update_plugins');
        }

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
        add_filter('site_transient_update_plugins', [$this, 'check_update']);
        add_filter('plugins_api', [$this, 'plugin_popup_info'], 20, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }

    private function get_repository_info() {
        if (!empty($this->github_response)) {
            return;
        }

        $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository);
        
        $args = [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
            ]
        ];

        $response = wp_remote_get($request_uri, $args);

        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $this->github_response = json_decode(wp_remote_retrieve_body($response), true);
        } else {
            $tags_uri = sprintf('https://api.github.com/repos/%s/%s/tags', $this->username, $this->repository);
            $tags_resp = wp_remote_get($tags_uri, $args);
            if (!is_wp_error($tags_resp) && 200 === wp_remote_retrieve_response_code($tags_resp)) {
                $tags = json_decode(wp_remote_retrieve_body($tags_resp), true);
                if (is_array($tags) && !empty($tags)) {
                    $latest_tag = $tags[0];
                    $this->github_response = [
                        'tag_name' => $latest_tag['name'],
                        'zipball_url' => sprintf('https://github.com/%s/%s/archive/refs/tags/%s.zip', $this->username, $this->repository, $latest_tag['name']),
                        'html_url' => sprintf('https://github.com/%s/%s/releases/tag/%s', $this->username, $this->repository, $latest_tag['name']),
                        'body' => 'New update release v' . $latest_tag['name']
                    ];
                }
            }
        }
    }

    public function check_update($transient) {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }

        $this->get_repository_info();

        if ($this->github_response && isset($this->github_response['tag_name'])) {
            $github_version = ltrim($this->github_response['tag_name'], 'v');

            if (version_compare($github_version, $this->version, '>')) {
                $package = isset($this->github_response['zipball_url']) ? $this->github_response['zipball_url'] : '';
                
                if (isset($this->github_response['assets']) && is_array($this->github_response['assets'])) {
                    foreach ($this->github_response['assets'] as $asset) {
                        if (isset($asset['browser_download_url']) && strpos($asset['browser_download_url'], '.zip') !== false) {
                            $package = $asset['browser_download_url'];
                            break;
                        }
                    }
                }

                $obj = new stdClass();
                $obj->slug = $this->slug;
                $obj->plugin = $this->plugin;
                $obj->new_version = $github_version;
                $obj->url = isset($this->github_response['html_url']) ? $this->github_response['html_url'] : '';
                $obj->package = $package;
                $obj->icons = [
                    'default' => 'https://cdn.trustindex.io/assets/platform/Google/icon.svg'
                ];

                $transient->response[$this->plugin] = $obj;
            }
        }

        return $transient;
    }

    public function plugin_popup_info($result, $action, $args) {
        if ('plugin_information' !== $action || !isset($args->slug) || ($args->slug !== $this->slug && $args->slug !== $this->plugin)) {
            return $result;
        }

        $this->get_repository_info();

        if ($this->github_response) {
            $github_version = ltrim($this->github_response['tag_name'], 'v');
            $res = new stdClass();
            $res->name = 'Google Review by Emon';
            $res->slug = $this->slug;
            $res->version = $github_version;
            $res->author = '<a href="https://github.com/' . $this->username . '">Emon</a>';
            $res->homepage = isset($this->github_response['html_url']) ? $this->github_response['html_url'] : '';
            $res->download_link = isset($this->github_response['zipball_url']) ? $this->github_response['zipball_url'] : '';
            $res->sections = [
                'description' => '100% Free & Standalone Google Reviews Plugin by Emon with Auto-Sync and Pro widgets.',
                'changelog' => isset($this->github_response['body']) ? $this->github_response['body'] : 'New updates and performance enhancements.'
            ];

            return $res;
        }

        return $result;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === $this->plugin) {
            $proper_destination = WP_PLUGIN_DIR . '/' . $this->slug;
            $wp_filesystem->delete($proper_destination, true);
            $wp_filesystem->move($result['destination'], $proper_destination, true);
            $result['destination'] = $proper_destination;
            activate_plugin($this->plugin);
        }

        return $result;
    }
}
