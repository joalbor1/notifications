<?php

/**
 * Elgg notifications group save
 *
 * @package ElggNotifications
 */

$current_user = elgg_get_logged_in_user_entity();

$guid = (int) get_input('guid', 0);
if (!$guid || !($user = get_entity($guid))) {
	forward();
}
if (($user->guid != $current_user->guid) && !$current_user->isAdmin()) {
	forward();
}

// Get group memberships and condense them down to an array of guids
$groups = array();
$options = array(
	'relationship' => 'member',
	'relationship_guid' => $user->guid,
	'type' => 'group',
	'limit' => 9999,
);
if ($groupmemberships = elgg_get_entities_from_relationship($options)) {
	foreach($groupmemberships as $groupmembership) {
		$groups[] = $groupmembership->guid;
	}
}

// Load important global vars
global $NOTIFICATION_HANDLERS;
foreach($NOTIFICATION_HANDLERS as $method => $foo) {
	$subscriptions[$method] = get_input($method.'subscriptions');
	$personal[$method] = get_input($method.'personal');
	$collections[$method] = get_input($method.'collections');
	if (!empty($groups)) {
		foreach($groups as $group) {
			if (in_array($group, $subscriptions[$method])) {
				add_entity_relationship($user->guid, 'notify'.$method, $group);
			} else {
				remove_entity_relationship($user->guid, 'notify'.$method, $group);
			}
		}
	}
}

system_message(elgg_echo('notifications:subscriptions:success'));

forward(REFERER);
