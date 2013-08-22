<?php if(!defined('APPLICATION')) exit();
/* 	Copyright 2013 Zachary Doll
 * 	This program is free software: you can redistribute it and/or modify
 * 	it under the terms of the GNU General Public License as published by
 * 	the Free Software Foundation, either version 3 of the License, or
 * 	(at your option) any later version.
 *
 * 	This program is distributed in the hope that it will be useful,
 * 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * 	GNU General Public License for more details.
 *
 * 	You should have received a copy of the GNU General Public License
 * 	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$PluginInfo['BulkInvite'] = array(
    'Title' => 'Bulk Invite',
    'Description' => 'A plugin in that provides an interface to invite users in bulk.',
    'Version' => '0.1',
    'RequiredApplications' => array('Vanilla' => '2.0.18.8'),
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => FALSE,
    'SettingsUrl' => '/settings/bulkinvite',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'Author' => "Zachary Doll",
    'AuthorEmail' => 'hgtonight@daklutz.com',
    'AuthorUrl' => 'http://www.daklutz.com',
    'License' => 'GPLv3'
);

class BulkInvite extends Gdn_Plugin {

  public function PluginController_BulkInvite_Create($Sender) {
    $this->_AddResources($Sender);
    $Sender->Form = new Gdn_Form();
    $Sender->Permission('Garden.Settings.Manage');

    $Sender->SetData('Title', T('Bulk Invite Users'));
    $Sender->SetData('InviteMessage', T('Hi Pal!
      
Check out the new community forum I\'ve just set up. It\'s a great place for us to chat with each other online.
    
Follow the link below to log in.'));
    
    $Sender->AddSideMenu('plugin/bulkinvite');

    $Sender->AddDefinition('TextEnterEmails', T('TextEnterEmails', 'Type email addresses separated by commas here'));
    
    if($Sender->Form->AuthenticatedPostBack()) {
      // Do invitations to new members.
      $Message = $Sender->Form->GetFormValue('InvitationMessage');
      $Message .= "\n\n" . Gdn::Request()->Url('/', TRUE);
      $Message = trim($Message);
      $Recipients = $Sender->Form->GetFormValue('Recipients');
      if($Recipients == $Sender->TextEnterEmails)
        $Recipients = '';

      $Recipients = explode(',', $Recipients);
      $CountRecipients = 0;
      foreach($Recipients as $Recipient) {
        if(trim($Recipient) != '') {
          $CountRecipients++;
          if(!ValidateEmail($Recipient))
            $Sender->Form->AddError(sprintf(T('%s is not a valid email address'), $Recipient));
        }
      }
      if($CountRecipients == 0)
        $Sender->Form->AddError(T('You must provide at least one recipient'));
      if($Sender->Form->ErrorCount() == 0) {
        $Email = new Gdn_Email();
        $Email->Subject(T('Check out my new community!'));
        $Email->Message($Message);
        foreach($Recipients as $Recipient) {
          if(trim($Recipient) != '') {
            $Email->To($Recipient);
            try {
              $Email->Send();
            } catch(Exception $ex) {
              $Sender->Form->AddError($ex);
            }
          }
        }
      }
      if($Sender->Form->ErrorCount() == 0)
        $Sender->InformMessage(T('Your invitations were sent successfully.'));
    }

    $Sender->Render($this->GetView('bulkinvite.php'));
  }

  //Add a link to the dashboard menu
  public function Base_GetAppSettingsMenuItems_Handler($Sender) {
    $Menu = &$Sender->EventArguments['SideMenu'];
    $Menu->AddLink('Users', 'Bulk Invite', 'plugin/bulkinvite', 'Garden.Settings.Manage');
  }

  private function _AddResources($Sender) {
    $Sender->AddJsFile($this->GetResource('js/bulkinvite.js', FALSE, FALSE));
    $Sender->AddCssFile($this->GetResource('design/bulkinvite.css', FALSE, FALSE));
  }

  public function Setup() {
    // SaveToConfig('Plugins.BulkInvite.EnableAdvancedMode', TRUE);
  }

  public function OnDisable() {
    // RemoveFromConfig('Plugins.BulkInvite.EnableAdvancedMode');
  }

}
