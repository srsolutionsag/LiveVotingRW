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
 * info@surlabs.esr
 *
 */

use LiveVoting\UI\LiveVotingChoicesUI;
use LiveVoting\UI\LiveVotingManageUI;
use LiveVoting\UI\LiveVotingUI;

/**
 * Class ilObjLiveVotingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjLiveVotingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI
 * @ilCtrl_Calls      ilObjLiveVotingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI
 */
class ilObjLiveVotingGUI extends ilObjectPluginGUI
{

    public function getType(): string
    {
        return "xlvo";
    }

    public function getAfterCreationCmd(): string
    {
        return 'showContentAfterCreation';
    }

    public function getStandardCmd(): string
    {
        return 'showContent';
    }

    /**
     * @throws ilCtrlException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;
        $DIC->help()->setScreenIdComponent(ilLiveVotingPlugin::PLUGIN_ID);
        //$cmd = $DIC->ctrl()->getCmd('showContent');
        $DIC->ui()->mainTemplate()->setPermanentLink(ilLiveVotingPlugin::PLUGIN_ID, $this->ref_id);

        $this->initHeaderAndLocator();

        switch ($cmd){
            case 'index':
                $this->showContent();
                break;
            case 'showContentAfterCreation':
            case 'editProperties':
            case 'manage':
            case 'selectType':
            case 'selectedChoices':
            case 'updateProperties':
                $this->{$cmd}();
                break;
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function showContentAfterCreation(): void
    {
        global $DIC;
        $liveVotingUI = new LiveVotingUI();
        //$this->setSubTabs('tab_content', 'subtab_show');
        $this->tabs->activateTab("tab_content");

        try {
            $this->tpl->setContent($liveVotingUI->showIndex());
        } catch (ilSystemStyleException|ilTemplateException $e) {
            //TODO: Mostrar error
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function showContent(): void
    {
        $liveVotingUI = new LiveVotingUI();
        $this->tabs->activateTab("tab_content");
        try {
            $this->tpl->setContent($liveVotingUI->showIndex());
        } catch (ilSystemStyleException|ilTemplateException $e) {
            //TODO: Mostrar error

        }
    }

    /**
     * @throws ilCtrlException|ilException
     */
    public function manage(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        if (!ilObjLiveVotingAccess::hasWriteAccess()) {
            $this->tpl->setContent("Error de acceso");
            //TODO: Mostrar error


        } elseif (ilObjLiveVotingAccess::hasWriteAccess()) {
            $liveVotingManageUI = new LiveVotingManageUI();
            try {
/*                $DIC->toolbar()->addComponent($DIC->ui()->factory()->button()->primary($this->txt('voting_add'), $this->ctrl->getLinkTarget($this, "selectType")));
                $DIC->toolbar()->addComponent($DIC->ui()->factory()->button()->standard($this->txt('voting_reset_all'), $this->ctrl->getLinkTarget($this, "selectType")));*/

                $this->tpl->setContent($liveVotingManageUI->showManage($this));
            } catch (ilSystemStyleException|ilTemplateException $e) {
                //TODO: Mostrar error
            }


        }
        //$this->tpl->setContent("Contenido de la pestaña de edición");
    }

    /**
     * @throws ilException
     */
    public function selectedChoices(): void
    {
        $this->tabs->activateTab("tab_edit");
        $id = 0;

        if (isset($_GET["id"])) {
            $id = (int) $_GET["id"];
        }

        $liveVotingChoicesUI = new LiveVotingChoicesUI($id);
        $this->tpl->setContent($liveVotingChoicesUI->renderChoicesForm());
    }

    protected function initHeaderAndLocator(): void
    {
        global $DIC;
        $this->setTitleAndDescription();
        if(!$this->getCreationMode()) {
            $DIC->ui()->mainTemplate()->setTitle($this->object->getTitle());
            $DIC->ui()->mainTemplate()->setTitleIcon(IlObject::_getIcon($this->object->getId()));
            //$DIC->ui()->saveParameterByClass("CLASE RESULTS, PARÁMETRO round_id");

            //TODO: Aquí hay un if en el original que comprueba si el parámetro baseClass es igual a la clase GUI de administración.
            //$this->setTabs();

            //TODO: Comprobación de permisos
        } else {
            $DIC->ui()->mainTemplate()->setTitle(ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)));
            //TODO: Añadir icono?
        }
        $this->setLocator();
    }

    /**
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        $this->tabs->addTab("tab_content", $this->lng->txt("tab_content"), $this->ctrl->getLinkTarget($this, "index"));
        $this->tabs->addTab("tab_manage", $this->plugin->txt("tab_manage"), $this->ctrl->getLinkTarget($this, "manage"));
        $this->tabs->addTab("info_short", $this->lng->txt('info_short'), $this->ctrl->getLinkTargetByClass(array(
            get_class($this),
            "ilInfoScreenGUI",
        ), "showSummary"));

        if ($this->checkPermissionBool("write")) {
            $this->tabs->addTab("tab_edit", $this->plugin->txt("tab_edit"), $this->ctrl->getLinkTarget($this, "editProperties"));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs->addTab("perm_settings", $this->lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(
                get_class($this),
                "ilPermissionGUI",
            ), "perm"));
        }
    }

    /**
     * Add sub tabs and activate the forwarded sub tab in the parameter.
     *
     * @param string $tab
     * @param string $active_sub_tab
     * @throws ilCtrlException
     */
    protected function setSubTabs(string $tab, string $active_sub_tab): void
    {
        if($tab == 'tab_content'){
            $this->tabs->addSubTab("subtab_show",
                $this->plugin->txt('subtab_show'),
                $this->ctrl->getLinkTarget($this, "index")
            );
            $this->tabs->addSubTab("subtab_edit",
                $this->plugin->txt('subtab_edit'),
                $this->ctrl->getLinkTarget($this, "content")
            );

//            if (ilObjLiveVotingAccess::hasWriteAccess()) {
//                $this->tabs->addSubTab("subtab_edit",
//                    $this->plugin->txt('subtab_edit'),
//                    $this->ctrl->getLinkTargetByClass("LiveVotingUI", "showContent")
//                );
//            }
        }

        $this->tabs->activateSubTab($active_sub_tab);
    }

/*    protected function triageCmdClass($next_class, $cmd): void
    {
        switch($next_class){
            default:
                if (strcasecmp($_GET['baseClass'], ilAdministrationGUI::class) == 0) {
                    $this->viewObject();
                    return;
                }
                if (!$cmd) {
                    $cmd = $this->getStandardCmd();
                }
                if ($this->getCreationMode()) {
                    $this->$cmd();
                } else {
                    $this->performCommand($cmd);
                }
                break;
        }
    }*/

    public function getObjId(): int
    {
        return $this->object->getId();
    }

    public function editProperties(): void
    {
        $this->tabs->activateTab("tab_edit");

        // TODO: Dani trabaja
    }
}