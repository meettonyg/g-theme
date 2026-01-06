<?php
/**
 * Template Part: Account Team Panel
 *
 * Displays the team members settings panel.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['user_data'] User data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user_data = isset($args['user_data']) ? $args['user_data'] : array();
$user_id = isset($args['user_id']) ? $args['user_id'] : get_current_user_id();

// Get team members (placeholder - would integrate with actual team functionality)
$team_members = guestify_get_team_members($user_id);
$pending_invitations = guestify_get_pending_invitations($user_id);
?>

<div id="team" class="gfy-panel" role="tabpanel">
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Team Members', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Manage who has access to this workspace.', 'guestify'); ?></p>
            </div>
            <button class="gfy-btn gfy-btn--primary" data-action="invite-member">
                <i class="fa-solid fa-plus"></i>
                <?php esc_html_e('Invite Member', 'guestify'); ?>
            </button>
        </div>
        <div class="gfy-card__body">
            <?php if (!empty($team_members)): ?>
                <?php foreach ($team_members as $member): ?>
                <div class="gfy-team-member">
                    <div class="gfy-team-member__info">
                        <?php if (!empty($member['avatar_url'])): ?>
                        <img src="<?php echo esc_url($member['avatar_url']); ?>"
                             alt="<?php echo esc_attr($member['name']); ?>"
                             class="gfy-avatar gfy-team-member__avatar">
                        <?php else: ?>
                        <div class="gfy-avatar gfy-team-member__avatar gfy-avatar--placeholder">
                            <?php echo esc_html($member['initials'] ?? 'U'); ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="gfy-team-member__name"><?php echo esc_html($member['name']); ?></div>
                            <div class="gfy-team-member__email"><?php echo esc_html($member['email']); ?></div>
                        </div>
                    </div>
                    <div class="gfy-team-member__actions">
                        <span class="gfy-team-member__role"><?php echo esc_html($member['role']); ?></span>
                        <?php if ($member['role'] !== 'Owner' && $member['id'] !== $user_id): ?>
                        <button class="gfy-btn gfy-btn--danger gfy-btn--sm" data-action="remove-member" data-member-id="<?php echo esc_attr($member['id']); ?>">
                            <?php esc_html_e('Remove', 'guestify'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Show current user as owner -->
                <div class="gfy-team-member">
                    <div class="gfy-team-member__info">
                        <?php if (!empty($user_data['avatar_url'])): ?>
                        <img src="<?php echo esc_url($user_data['avatar_url']); ?>"
                             alt="<?php echo esc_attr($user_data['display_name']); ?>"
                             class="gfy-avatar gfy-team-member__avatar">
                        <?php else: ?>
                        <div class="gfy-avatar gfy-team-member__avatar gfy-avatar--placeholder">
                            <?php echo esc_html($user_data['initials'] ?? 'U'); ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="gfy-team-member__name"><?php echo esc_html($user_data['display_name'] ?? 'You'); ?></div>
                            <div class="gfy-team-member__email"><?php echo esc_html($user_data['email'] ?? ''); ?></div>
                        </div>
                    </div>
                    <span class="gfy-team-member__role"><?php esc_html_e('Owner', 'guestify'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Pending Invitations', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e("Invitations that haven't been accepted yet.", 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <?php if (!empty($pending_invitations)): ?>
                <?php foreach ($pending_invitations as $invitation): ?>
                <div class="gfy-team-member">
                    <div class="gfy-team-member__info">
                        <div class="gfy-avatar gfy-team-member__avatar gfy-avatar--upload-placeholder">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div class="gfy-team-member__email"><?php echo esc_html($invitation['email']); ?></div>
                            <div class="gfy-helper-text"><?php printf(esc_html__('Invited %s', 'guestify'), $invitation['date']); ?></div>
                        </div>
                    </div>
                    <div class="gfy-team-member__actions">
                        <button class="gfy-btn gfy-btn--secondary gfy-btn--sm" data-action="resend-invitation" data-invitation-id="<?php echo esc_attr($invitation['id']); ?>">
                            <i class="fa-solid fa-paper-plane"></i>
                            <?php esc_html_e('Resend', 'guestify'); ?>
                        </button>
                        <button class="gfy-btn gfy-btn--danger gfy-btn--sm" data-action="cancel-invitation" data-invitation-id="<?php echo esc_attr($invitation['id']); ?>">
                            <?php esc_html_e('Cancel', 'guestify'); ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="gfy-empty-state">
                <div class="gfy-empty-state__icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <h3 class="gfy-empty-state__title"><?php esc_html_e('No Pending Invitations', 'guestify'); ?></h3>
                <p class="gfy-empty-state__desc"><?php esc_html_e('When you invite team members, they will appear here until they accept.', 'guestify'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
