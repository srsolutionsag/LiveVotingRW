<?php
declare(strict_types=1);
/**
 * This file is part of the LiveVoting Repository Object plugin for ILIAS.
 * This plugin allows to create real time votings within ILIAS.
 *
 * The LiveVoting Repository Object plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "LiveVoting" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/LiveVoting
 *
 * If you need support, please contact the maintainer of this software at:
 * info@surlabs.es
 *
 */

use ILIAS\DI\Container;

use LiveVoting\platform\ilias\LiveVotingContext;
use LiveVoting\platform\LiveVotingConfig;
use LiveVoting\player\LiveVotingInitialisationUI;
use LiveVoting\player\LiveVotingPlayerUI;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingParticipant;
require_once __DIR__ . '/../../../../../../../libs/composer/vendor/autoload.php';
/*require_once __DIR__ . "/vendor/autoload.php";*/
require_once "dir.php";




try {
    LiveVotingInitialisationUI::init();

    LiveVotingContext::setContext(1);

    LiveVotingParticipant::getInstance()->setIdentifier(session_id())->setType(2);

    $param_manager = ParamManager::getInstance();

    $pin = $param_manager->getPin();

    global $DIC;

    $DIC->ctrl()->setTargetScript(LiveVotingConfig::getFullApiUrl());

    if(!empty($pin)) {
        $liveVoting = LiveVoting::getLiveVotingFromPin($pin);

        if ($liveVoting) {
            if ($liveVoting->isOnline()) {
                if ($liveVoting->isAnonymous() || LiveVotingParticipant::getInstance()->isPINUser()) {
                    $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, LiveVotingPlayerUI::class], 'startVoterPlayer');
                } else {
                    $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, LiveVotingPlayerUI::class], 'votingNeedLogin');
                }
            } else {
                $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, LiveVotingPlayerUI::class], 'votingOffline');
            }
        } else {
            $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, LiveVotingPlayerUI::class], 'votingNotFound');
        }
    } else {
        $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, LiveVotingPlayerUI::class], 'index');
    }
} catch (Throwable $ex) {
    echo $ex->getMessage() . "<br /><br /><a href='/'>back</a>";
}
