<?php

class WPSTActivation
{
    function __construct()
    {
        add_action('activated_plugin', array($this, 'add_roles'));
    }
    public function add_roles()
    {
        // Client
        $client_role = get_role('sendtrace_client');
        if (!$client_role) {
            add_role('sendtrace_client', 'SendTrace Client', array(
                'read' => true,
            ));
        }
        
        // Editor
        $editor_role = get_role('sendtrace_editor');
        if (!$editor_role) {
            add_role('sendtrace_editor', 'SendTrace Editor', array(
                'read' => true,
                'create_posts' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'delete_posts' => false
            ));
        }     
        
        // Agent
        $editor_role = get_role('sendtrace_agent');
        if (!$editor_role) {
            add_role('sendtrace_agent', 'SendTrace Agent', array(
                'read' => true,
                'create_posts' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'delete_posts' => false
            ));
        }     
    }
}
new WPSTActivation;