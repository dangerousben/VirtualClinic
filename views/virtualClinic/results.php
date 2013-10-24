<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
?>

<?php
//function aasort (&$array, $key) {
//    $sorter=array();
//    $ret=array();
//    reset($array);
//    foreach ($array as $ii => $va) {
//        $sorter[$ii]=$va[$key];
//    }
//    asort($sorter);
//    foreach ($sorter as $ii => $va) {
//        $ret[$ii]=$array[$ii];
//    }
//    return $ret;
//}
//
//// sort by date?
//if ($sort_by == 6) {
//  $array = aasort($dataProvider->getData(),"visit_date");
//} else if ($sort_by == 8) { // sort by review
//  $array = aasort($dataProvider->getData(),"reviewed");
//}
//else {
  $array = $dataProvider->getData();
//}

$clinic = new VirtualClinic();
$clinics = $clinic->getClinics();
$sites = Site::model()->findAll();

$siteStr = "No site selected";
$clinicStr = "No clinic selected";

if ($site_id > 0 && isset($sites[$site_id - 1])) {
  $siteStr = "Site: " . $sites[$site_id - 1]->name;
}
if ($virtual_clinic_id > 0 && isset($clinics[$virtual_clinic_id])) {
  $clinicStr = "Clinic: " . $clinics[$virtual_clinic_id]->name;
}
echo CHtml::hiddenField('YII_CSRF_TOKEN',Yii::app()->request->csrfToken);
?>
<input type="hidden" value="<?php echo $site_id ?>" id='site_id' >
<input type="hidden" value="<?php echo $clinics[$virtual_clinic_id]->subspecialty_id ?>" id='subspeciality_id' >
<input type="hidden" value="<?php echo $virtual_clinic_id ?>" id='clinic_id' >
<!--<p><strong><?php // echo $siteStr   ?>, <?php // echo $clinicStr   ?></strong>-->
<div class="wrapTwo clearfix">
  <div >

    <?php
    if (isset($total_items) && $total_items > 0) {
      ?>

      <p><strong>Virtual Clinic</strong>: <?php echo $total_items ?> patients found
        <br/><br/>Site <select id="urlList" onchange="window.location.href = this.value">
          <option value="">- Please select -</option>
          <?php
          $sites = Site::model()->findAll();
          foreach ($sites as $site) {
            if ($site->id != $site_id) {
              ?>
              <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $virtual_clinic_id ?>/"><?php echo $site->short_name ?></option>
              <?php
            } else {
              ?>
              <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $virtual_clinic_id ?>/" selected><?php echo $site->short_name ?></option>
              <?php
            }
          }
          ?>
        </select><br/><br/>
        Clinic <select id="urlList" onchange="window.location.href = this.value">
          <option value="">- Please select -</option>
          <?php
          foreach ($clinics as $c_id => $clinic) {
            if ($virtual_clinic_id != $c_id) {
              ?>
              <option value = "/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $c_id ?>/"><?php echo $clinic->name ?></option>
              <?php
            } else {
              ?>
              <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $c_id ?>/" selected><?php echo $clinic->name ?></option>
              <?php
            }
          }
          ?>
        </select>
      </p>

      <?php $this->renderPartial('//base/_messages'); ?>

      <div class="whiteBox">
        <?php
        $from = (($pagen -1) * $items_per_page) + 1;
        $to = ($pagen) * $items_per_page;
        if ($to > $total_items) {
          $to = $total_items;
        }
        $x = $clinics;
        $y = $virtual_clinic_id;
        $mainIndex = 'ID';
        $useCrn = Yii::app()->params['virtualClinic.index.CRN'] ? true : false;
        ?>
        <h3>Results for <?php echo $clinics[$virtual_clinic_id]->name ?> Clinic, <?php echo $sites[$site_id - 1]->name ?>. You are viewing patients <?php echo $from ?> to <?php echo $to ?>, of <?php echo $total_items ?></h3>

        <div id="patient-grid" class="grid-view">
          <table class="items">
            <thead>
              <tr>
                <th style="display: none" id="patient-grid_c0"></th>
                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/0/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>"><?php echo $mainIndex?></a></th>
                <th id="patient-grid_c0"><!--a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/1/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">First Name</a-->First Name</th>
                <th id="patient-grid_c0"><!--a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/2/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">Last Name</a-->Last Name</th>
                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/3/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">Age</a></th>
                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/4/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">S/B</a></th>
                <!-- speciality-specific column headings: -->
                <?php
                $cols = VirtualClinic::model()->getColumnNames($clinics[$virtual_clinic_id]);
                foreach ($cols as $col_index => $column) {
                  ?>
              <!--                                    <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php // echo $pagen       ?>/<?php // if ($sort_dir == 0) {       ?>1<?php // } else {       ?>0<?php // }       ?>/11/<?php // echo $site_id       ?>/<?php // echo $virtual_clinic_id       ?>"><?php // echo $column       ?></a></th>-->
                  <th id="patient-grid_c0"><?php echo $column ?></th>
                <?php }
                ?>
                <!-- end of speciality-specific column headings -->
                <th id="patient-grid_c0" width="8%"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/6/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">Date</a></th>
  <!--										<th id="patient-grid_c5">Site</th>-->
                <th id="patient-grid_c5" width="3%">5yr Risk</th>
                <th id="patient-grid_c5" width="3%"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/7/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">Flag</a></th>
                <th id="patient-grid_c5" width="3%"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/8/<?php echo $site_id ?>/<?php echo $virtual_clinic_id ?>">Reviewed</a></th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($array as $i => $result) {
                ?>
                <tr class="<?php if ($i % 2 == 0) { ?>even<?php } else { ?>odd<?php } ?>">
                  <?php
                  if ($useCrn) {
                    $searchIndex = $result->patient->nhs_num;
                  } else {
                    $searchIndex = $result->patient->hos_num;
                  }
                  // need to check of there is an episode for this firm:

                  $firm_id = $result->firm_id;
                  $user_id = $result->seen_by_user_id;
                  $criteria = new CDbCriteria;
                  $criteria->condition = 'firm_id=' . $firm_id . ' and patient_id=' . $result->patient->id;
                  $criteria->order = 'id DESC';
                  $criteria->distinct = true;
                  $episode = Episode::model()->find($criteria);
                  if ($episode != null) {
                    ?>
                    <td style="display: none; vertical-align:middle"><?php echo $episode->id ?></td>
                    <td style="display: none; vertical-align:middle"><?php echo $result->patient->id ?></td>
                    <?php
                  }
                  ?>
                  <td style="vertical-align:middle"><?php echo $searchIndex ?></td>
                  <td style="vertical-align:middle"><?php echo $result->patient->contact->first_name ?></td>
                  <td style="vertical-align:middle"><?php echo $result->patient->contact->last_name ?></td>
                  <td style="vertical-align:middle"><?php echo $result->patient->getAge() ?></td>
                  <td style="vertical-align:middle">
                    <?php
                    $user = User::model()->findByPk($user_id);
                    if (isset($user)) {
                      echo $user->first_name[0] . '. ' . $user->last_name;
                    }
                    ?></td>
                  <!-- speciality-specific column values: -->
                  <?php
                  
                $cols = VirtualClinic::model()->getColumnNames($clinics[$virtual_clinic_id]);
                  foreach ($cols as $col_index => $column) {
                    $value = $clinic->getColumnValue($result->patient->id, $clinics[$virtual_clinic_id], $column);
                    ?>
                    <td style="vertical-align:middle"><?php echo $clinic->formatTableData($clinics[$virtual_clinic_id], $column, $value) ?></td>
                  <?php }
                  ?>
                  <!-- end of speciality-specific column values -->

                  <td style="vertical-align:middle"><?php
              if ($result->visit_date) {
                $date = new DateTime($result->visit_date);
                echo $date->format('Y-m-d');
              }
                  ?></td>
                  <!--td style="vertical-align:middle">ODTC</td-->
                  <td style="vertical-align:middle">0%<?php ?></td>
                  <td style="vertical-align:middle"><input id="checkbox_flagged" type="checkbox" name="flagged"
                                                           <?php 
                                                           $flagged = '';
                                                           $x = $result->patient->hos_num;
                                                           if ($result->flag) {
                                                             $flagged = 'checked';
                                                           }
                                                           echo $flagged ?>
                                                           value="flagged" onclick="var subspeciality_id = $('#subspeciality_id').val();
                                                             var clinic_id = $('#clinic_id').val();
                                                             var site_id = $('#site_id').val();
                                                             var selected=$(this).is(':checked');
                                                             var csrf = $('#YII_CSRF_TOKEN').val();
                                                             var pid=$(this).parent().parent().children(':nth-child(2)').html();
                                                             $.post('/virtualClinic/flag/'+ pid + '/' + selected + '/' + clinic_id + '/' + site_id + '/' + subspeciality_id, {'YII_CSRF_TOKEN':csrf});" /><?php // echo $result->nhs_num        ?></td>
                                                             
                  <td style="vertical-align:middle"><input id="checkbox_reviewed" type="checkbox" name="reviewed"
                                                           <?php 
                                                           $reviewed = '';
                                                           $x = $result->patient->hos_num;
                                                           if ($result->reviewed) {
                                                             $reviewed = 'checked';
                                                           }
                                                           echo $reviewed ?>
                                                           value="reviewed" onclick="var subspeciality_id = $('#subspeciality_id').val();
                                                             var clinic_id = $('#clinic_id').val();
                                                             var site_id = $('#site_id').val();
                                                             var selected=$(this).is(':checked');
                                                             var csrf = $('#YII_CSRF_TOKEN').val();
                                                             var pid=$(this).parent().parent().children(':nth-child(2)').html();
                                                             $.post('/virtualClinic/review/'+ pid + '/' + selected + '/' + clinic_id + '/' + site_id + '/' + subspeciality_id, {'YII_CSRF_TOKEN':csrf});" /><?php // echo $result->nhs_num        ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

        <div class="resultsPagination">
          <?php for ($i = 0; $i < $pages; $i++) { ?>
            <?php
            if ($i == $pagen - 1) {
              $to = ($i + 1) * $items_per_page;
              if ($to > $total_items) {
                $to = $total_items;
              }
              ?>
              <span class="showingPage"><?php echo 1 + ($i * $items_per_page) ?> - <?php echo $to ?></span>
            <?php } else { ?>
              <?php
              $to = ($i + 1) * $items_per_page;
              if ($to > $total_items) {
                $to = $total_items;
              }
              ?>
              <!--span class="otherPages"><a href="/virtualClinic/results/<?php // echo $first_name      ?>/<?php // echo $last_name      ?>/<?php // echo $nhs_num      ?>/<?php // echo $gender      ?>/<?php // echo $sort_by      ?>/<?php // echo $sort_dir      ?>/<?php // echo $i+1      ?>"><?php // echo 1+($i*$items_per_page)      ?> - <?php // echo $to      ?></a></span-->
              <span class="otherPages"><a href="/virtualClinic/results/<?php echo $i + 1 ?>/<?php if ($sort_dir == 0) { ?>0<?php } else { ?>1<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id?>/<?php echo $virtual_clinic_id?>"><?php echo 1 + ($i * $items_per_page) ?> - <?php echo $to ?></a></span>
            <?php } ?>
          <?php } ?>
        </div>

      </div> <!-- .whiteBox -->

    </div>	<!-- .wideColumn -->

  </div><!-- .wrapTwo -->
  <script type="text/javascript">
    $('#patient-grid .items tr td').click(function() {
      /* OK - so different clinics will have different column requirements,
       * which means that we need to calculate the values of the last two
       * columns based on the clinic's column count (clicking non-checkboxes
       * takes the user to the episode ot patient summary; so we need to
       * calculate the value of the last two checkboxes, 'review' and 'flag'):
       */
      if ($(this).parent().children().index($(this)) > (8 + <?php 
      
      echo count(Yii::app()->params['virtualClinic.columns'][$clinic->name]) ;
              ?>)) {
        return true;
      }
  <?php
  // need to check of there is an episode for this firm:
  $criteria = new CDbCriteria;
  $criteria->condition = 'firm_id=' . $firm_id;
  $criteria->order = 'id DESC';
  $criteria->distinct = true;
  $episode = Episode::model()->find($criteria);
  if ($episode) {
    ?>
                    window.location.href = '/patient/episode/' + $(this).parent().children(":first").html();
    <?php
  } else {
    ?>
                //                                    window.location.href = '/patient/viewhosnum/'+$(this).parent().children(":first").html();
                window.location.href = '/<?php echo preg_replace("/[\s\W]/", "", preg_replace("/[^A-Za-z0-9]/", "", $clinics[$virtual_clinic_id])) ?>VirtualClinic/default/view/'+$(this).parent().children(":first").html();
    <?php
  }
  ?>
              return false;
            });
  </script>
  <?php
} else {
  ?>
  <p><strong>No patients found; select a clinic.</strong>
    <select id="urlList" onchange="window.location.href = this.value">
      <option value="">- Please select -</option>
      <?php
      foreach ($sites as $site) {
        ?>
        <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $virtual_clinic_id ?>/"><?php echo $site->short_name ?></option>
      <?php } ?>
    </select>
    <select id="urlList" onchange="window.location.href = this.value">
      <option value="">- Please select -</option>
      <?php
      foreach ($clinics as $virtual_clinic) {
        ?>
        <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $virtual_clinic->id ?>/"><?php echo $virtual_clinic->name ?></option>
      <?php } ?>
    </select></p>
  <?php
}
?>
