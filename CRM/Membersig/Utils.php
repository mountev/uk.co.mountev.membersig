<?php

class CRM_Membersig_Utils {

  const CG_MEMBER_SIG_ACCESS_DATES = "Member_SIG_Access_Dates";
  const PURCHASE_EXTRA_SIG_PAGE_IDS = [8,11];

  public static function getContributionId($membershipId) {
    $query = "SELECT MAX(contribution_id) FROM civicrm_membership_payment WHERE membership_id = %1";
    return CRM_Core_DAO::singleValueQuery($query, [1 => [$membershipId, 'Integer']]);
  }

  public static function getContributionFT($contributionId) {
    return CRM_Core_DAO::getFieldValue('CRM_Contribute_BAO_Contribution', $contributionId, 'financial_type_id');
  }

  public static function getLineItemFTs($contributionId) {
    $lifts  = [];
    $result = civicrm_api3('LineItem', 'get', [
      'sequential' => 1,
      'entity_table' => "civicrm_contribution",
      'entity_id'    => $contributionId,
    ]);
    if (!empty($result['values'])) {
      $financialTypes = CRM_Financial_BAO_FinancialType::getAvailableFinancialTypes();
      foreach ($result['values'] as $item) {
        $lifts[$item['price_field_id']] = $financialTypes[$item['financial_type_id']];
      }
    }
    return $lifts;
  }

  public static function getAccessDateFieldsRelatedToLineItemFTs($contributionId) {
    static $cfids = [];
    if (empty($cfids)) {
      $customFields = [];
      $result = civicrm_api3('CustomField', 'get', [
        'sequential' => 1,
        'custom_group_id' => self::CG_MEMBER_SIG_ACCESS_DATES,
      ]);
      if (!empty($result['values'])) {
        $customFields = $result['values'];
      }
      foreach ($customFields as $field) {
        $customFieldIds[$field['label']] = $field['id'];
      }
      $lifts = self::getLineItemFTs($contributionId);
      foreach ($lifts as $priceFieldId => $ftLabel) {
        if (!empty($customFieldIds[$ftLabel])) {
          $cfids[$priceFieldId] = $customFieldIds[$ftLabel];
        }
      }
    }
    return $cfids;
  }

  public static function getMembershipTypes($membershipId) {
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ["extends_entity_column_value"],
      'name'   => self::CG_MEMBER_SIG_ACCESS_DATES,
    ]);
    if (!empty($result['values'][0]['extends_entity_column_value'])) {
      return $result['values'][0]['extends_entity_column_value'];
    }
    return [];
  }

  public static function updateAccessDates($contributionId, $membershipId, $date) {
    $cfids = self::getAccessDateFieldsRelatedToLineItemFTs($contributionId);
    CRM_Core_Error::debug_var('SIG $cfids', $cfids);
    foreach ($cfids as $cfid) {
      $params = [
        'entity_id' => $membershipId,
        "custom_{$cfid}" => CRM_Utils_Date::customFormat($date, '%Y%m%d'),
      ];
      CRM_Core_Error::debug_var('SIG update membership access date $params', $params);
      civicrm_api3('CustomValue', 'create', $params);
    }
  }
}
