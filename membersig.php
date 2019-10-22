<?php

require_once 'membersig.civix.php';
use CRM_Membersig_ExtensionUtil as E;
use CRM_Membersig_Utils as MS;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/ 
 */
function membersig_civicrm_config(&$config) {
  _membersig_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function membersig_civicrm_xmlMenu(&$files) {
  _membersig_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function membersig_civicrm_install() {
  _membersig_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function membersig_civicrm_postInstall() {
  _membersig_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function membersig_civicrm_uninstall() {
  _membersig_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function membersig_civicrm_enable() {
  _membersig_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function membersig_civicrm_disable() {
  _membersig_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function membersig_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membersig_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function membersig_civicrm_managed(&$entities) {
  _membersig_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function membersig_civicrm_caseTypes(&$caseTypes) {
  _membersig_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function membersig_civicrm_angularModules(&$angularModules) {
  _membersig_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function membersig_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _membersig_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function membersig_civicrm_entityTypes(&$entityTypes) {
  _membersig_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function membersig_civicrm_themes(&$themes) {
  _membersig_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function membersig_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function membersig_civicrm_navigationMenu(&$menu) {
  _membersig_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _membersig_civix_navigationMenu($menu);
} // */

function membersig_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // where membership is attached to a contribution
  // the reason we don't do it with Contribution object is - hook may be called before
  // membership is updated and we may not have end-date available.
  if (($op == 'create' OR $op == 'edit') AND $objectName == 'Membership' ) {
    $result = civicrm_api3('Membership', 'get', [
      'sequential' => 1,
      'id' => $objectId,
    ]);
    if (!empty($result['values'][0]['end_date']) && 
      in_array($result['values'][0]['membership_type_id'], MS::getMembershipTypes($objectId))
    ) {
      $contributionId = MS::getContributionId($objectId);
      if ($contributionId) {
        MS::updateAccessDates($contributionId, $objectId, $result['values'][0]['end_date']);
      }
    }
  } 

  // where membership is not attached to a contribution. And contribution is for purchasing
  // extra SIG
  if (($op == 'create' OR $op == 'edit') AND $objectName == 'Contribution' ) {
    _membersig_civicrm_extra_sig_purchase($objectRef->contact_id, $objectId, $objectRef->contribution_status_id, $objectRef->contribution_page_id);
  }
}

function _membersig_civicrm_extra_sig_purchase($contactId, $contributionId, $contributionStatusId, $contributionPageId) {
  if (in_array($contributionPageId, MS::PURCHASE_EXTRA_SIG_PAGE_IDS)) {
    $statusCompleted = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
    if ($contributionStatusId == $statusCompleted) {
      $result = civicrm_api3('Membership', 'get', [
        'sequential' => 1,
        'contact_id' => $contactId ? $contactId : 0,
      ]);
      if (!empty($result['values'])) {
        foreach ($result['values'] as $mem) {
          if (in_array($mem['membership_type_id'], MS::getMembershipTypes($mem['id']))) {
            MS::updateAccessDates($contributionId, $mem['id'], $mem['end_date']);
          }
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function membersig_civicrm_postProcess($formName, &$form) {
  // AdditionalPayment form invokes contribution pre/post hook with status as Partially Paid.
  // Even though later it completes it when amount matches to total.
  // As a workwound we use postprocess hook to check the final status of contribution and trigger
  // sig_purchase functionality.
  if ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
    $contributionId = $form->getVar('_contributionId');
    $dao = new CRM_Contribute_DAO_Contribution();
    $dao->id = $contributionId;
    if ($dao->find(TRUE)) {
      _membersig_civicrm_extra_sig_purchase($dao->contact_id, $dao->id, $dao->contribution_status_id, $dao->contribution_page_id);
    }
  }
}
