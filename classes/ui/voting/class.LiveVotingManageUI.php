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

namespace LiveVoting\UI;

use Exception;
use ilCtrlException;
use ilCtrlInterface;
use ilException;
use ilHtmlPurifierFactory;
use ilHtmlPurifierNotFoundException;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLiveVotingPlugin;
use ilObject;
use ilObjLiveVotingGUI;
use ilPlugin;
use ilPropertyFormGUI;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use ilTextAreaInputGUI;
use LiveVoting\legacy\liveVotingTableGUI;
use LiveVotingQuestion;
use LiveVotingQuestionOption;

/**
 * Class LiveVotingChoicesUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 */
class LiveVotingManageUI
{
    /**
     * @var ilCtrlInterface
     */
    protected ilCtrlInterface $control;
    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @var ilPlugin
     */
    protected ilPlugin $plugin;
    protected renderer $renderer;
    protected $request;

    public function __construct()
    {
        global $DIC;

        $this->plugin = ilLiveVotingPlugin::getInstance();
        $this->control = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     */
    public function showManage($parent): string{
        global $DIC;

        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $ico = $f->symbol()->icon()->standard('', '')->withSize('medium')->withAbbreviation('+');
        $image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
        $page = $f->modal()->lightboxImagePage($image, 'Mountains');
        $modal = $f->modal()->lightbox($page);

        $glyph = $f->symbol()->glyph()->add("#");

        $button = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_1").' <br/><small><muted>('.$this->plugin->txt("voting_type_1_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedChoices'));
        $button2 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_2").' <br/><small><muted>('.$this->plugin->txt("voting_type_2_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType2'));
        $button3 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_4").' <br/><small><muted>('.$this->plugin->txt("voting_type_4_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType4'));
        $button4 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_5").' <br/><small><muted>('.$this->plugin->txt("voting_type_5_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType5'));
        $button5 = $f->button()->bulky($glyph, '<div style="margin-left:10px">'.$this->plugin->txt("voting_type_6").' <br/><small><muted>('.$this->plugin->txt("voting_type_6_info").')</muted></small></div>', $this->control->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'selectedType6'));


        $uri = new \ILIAS\Data\URI('https://ilias.de');
        $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);
        $divider = $f->divider()->horizontal();

        $items = [
            $f->menu()->sub($this->plugin->txt('voting_add'), [$button, $button2, $button3, $button4, $button5]),

            $f->menu()->sub($this->plugin->txt('voting_reset_all'), [
                $f->menu()->sub('Otter', [$button, $link]),
                $f->menu()->sub('Mole', [$button, $link]),
                $divider,
                $f->menu()->sub('Deer', [$button, $link])
            ])
        ];

        $dd = $f->menu()->drilldown('Manage Votings (NO TRANSLATED)', $items);

        $liveVotingTableGUI = new LiveVotingTableGUI($parent, 'showManage');

        return $renderer->render($dd).$liveVotingTableGUI->getHTML();

    }

}