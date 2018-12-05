<?php

require_once 'updatefinancialtype.civix.php';
use CRM_Updatefinancialtype_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function updatefinancialtype_civicrm_config(&$config) {
  _updatefinancialtype_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function updatefinancialtype_civicrm_xmlMenu(&$files) {
  _updatefinancialtype_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function updatefinancialtype_civicrm_install() {
  _updatefinancialtype_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function updatefinancialtype_civicrm_postInstall() {
  _updatefinancialtype_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function updatefinancialtype_civicrm_uninstall() {
  _updatefinancialtype_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function updatefinancialtype_civicrm_enable() {
  _updatefinancialtype_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function updatefinancialtype_civicrm_disable() {
  _updatefinancialtype_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function updatefinancialtype_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _updatefinancialtype_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function updatefinancialtype_civicrm_managed(&$entities) {
  _updatefinancialtype_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function updatefinancialtype_civicrm_caseTypes(&$caseTypes) {
  _updatefinancialtype_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function updatefinancialtype_civicrm_angularModules(&$angularModules) {
  _updatefinancialtype_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function updatefinancialtype_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _updatefinancialtype_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function updatefinancialtype_civicrm_entityTypes(&$entityTypes) {
  _updatefinancialtype_civix_civicrm_entityTypes($entityTypes);
}

/**
 * @param $op
 * @param $objectName
 * @param $objectId
 * @param $objectRef
 *
 * if the FT associated with line items is different from the FT assigned to
 * the contrib record, this will help bring them in line when subsequent payments
 * are recorded. it's imperfect, as the contrib can only store one FT but may
 * have line items with multiple FTs. but it improves the behavior.
 */
function updatefinancialtype_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  /*Civi::log()->debug('', [
    '$op' => $op,
    '$objectName' => $objectName,
    '$objectId' => $objectId,
    '$objectRef' => $objectRef,
  ]);*/

  if ($op == 'create' && $objectName == 'EntityFinancialTrxn') {
    if ($objectRef->entity_table = 'civicrm_financial_item') {
      $fts = CRM_Core_DAO::executeQuery("
        SELECT c.id contrib_id, li.financial_type_id li_ft, c.financial_type_id c_ft
        FROM civicrm_entity_financial_trxn eft
        JOIN civicrm_financial_item fi
          ON eft.entity_id = fi.id
          AND eft.entity_table = 'civicrm_financial_item'
        JOIN civicrm_line_item li
          ON fi.entity_id = li.id
          AND fi.entity_table = 'civicrm_line_item'
        JOIN civicrm_contribution c
          ON li.contribution_id = c.id
        WHERE eft.id = %1
          AND li.financial_type_id != c.financial_type_id
          AND c.id IS NOT NULL
        LIMIT 1
      ", [
        1 => [$objectId, 'Positive'],
      ]);

      while ($fts->fetch()) {
        //Civi::log()->debug('updatefinancialtype_civicrm_post', ['$fts' => $fts]);

        if ($fts->li_ft != $fts->c_ft) {
          //FT Ids do not match; update contrib FT
          try {
            /*civicrm_api3('contribution', 'create', [
              'id' => $fts->contrib_id,
              'financial_type_id' => $fts->li_ft,
            ]);*/
            CRM_Core_DAO::executeQuery("
              UPDATE civicrm_contribution
              SET financial_type_id = %1
              WHERE id = %2
            ", [
              1 => [$fts->li_ft, 'Positive'],
              2 => [$fts->contrib_id, 'Positive'],
            ]);
          }
          catch (CiviCRM_API3_Exception $e) {}
        }
      }
    }
  }
}
