<?php

return [

    // Navigation & Resource
    'navigation_group' => 'Authentication',
    'navigation_label' => 'Magic Links',
    'resource_label' => 'Magic Login Token',
    'resource_plural_label' => 'Magic Login Tokens',

    // Login Page
    'login_heading' => 'Sign in with Magic Link',
    'login_subheading' => 'Enter your email and we\'ll send you a secure login link.',
    'email_label' => 'Email address',
    'email_placeholder' => 'you@example.com',
    'send_link_button' => 'Send Magic Link',
    'back_to_password' => 'Back to password login',
    'link_sent_title' => 'Check your email!',
    'link_sent_body' => 'If an account exists with that email, we\'ve sent a magic login link.',

    // Hint Action
    'hint_action_label' => 'Login with magic link',

    // Action
    'action_label' => 'Send Magic Link',
    'action_modal_heading' => 'Send Magic Link',
    'action_modal_description' => 'Enter the email to send a magic login link.',
    'action_success' => 'Magic link sent successfully.',
    'action_user_not_found' => 'No user found with this email address.',
    'action_throttled' => 'Too many requests. Please try again later.',

    // Resource Columns
    'column_user' => 'User',
    'column_guard' => 'Guard',
    'column_status' => 'Status',
    'column_uses' => 'Uses',
    'column_ip_address' => 'IP Address',
    'column_expires_at' => 'Expires At',
    'column_created_at' => 'Created At',
    'column_last_used_at' => 'Last Used',

    // Status Badges
    'status_active' => 'Active',
    'status_expired' => 'Expired',
    'status_used' => 'Used',

    // Resource Actions
    'action_invalidate' => 'Invalidate',
    'action_cleanup' => 'Cleanup Expired',
    'action_cleanup_confirm' => 'This will permanently delete all expired tokens.',
    'action_cleanup_success' => 'Cleaned up :count expired tokens.',
    'invalidated_success' => 'Token invalidated.',
    'generate_success' => 'Magic link generated and sent.',

    // Resource Form
    'form_user' => 'User',
    'form_guard' => 'Guard',
    'form_redirect_url' => 'Redirect URL',
    'form_expiry_minutes' => 'Expiry (minutes)',
    'form_max_uses' => 'Max Uses',
    'form_send_notification' => 'Send email notification',

    // Widgets
    'widget_stats_heading' => 'Magic Link Stats',
    'widget_total_generated' => 'Total Generated',
    'widget_total_used' => 'Successfully Used',
    'widget_total_expired' => 'Expired Unused',
    'widget_total_active' => 'Active Links',
    'widget_chart_heading' => 'Magic Links Over Time',
    'widget_chart_generated' => 'Generated',
    'widget_chart_used' => 'Used',
    'widget_chart_failed' => 'Failed',
    'widget_top_users_heading' => 'Top Users by Magic Links',
    'widget_links_generated' => 'Links Generated',
    'widget_links_used' => 'Links Used',
    'widget_success_rate' => 'Success Rate',
    'widget_last_generated' => 'Last Generated',
    'widget_trend_up' => '+:percent% from last week',
    'widget_trend_down' => ':percent% from last week',
    'widget_expired_unused' => 'Expired without use',
    'widget_currently_valid' => 'Currently valid links',

    // Filter
    'filter_from' => 'From',
    'filter_until' => 'Until',

];
