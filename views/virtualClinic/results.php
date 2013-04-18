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
$clinic = new VirtualClinic();
$clinics = $clinic->getClinics();
$sites = Site::model()->findAll();

$siteStr = "No site selected";
$clinicStr = "No clinic selected";

if ($site_id > 0 && isset($sites[$site_id - 1])) {
    $siteStr = "Site: " . $sites[$site_id - 1]->name;
}
if ($clinic_id > 0 && isset($clinics[$clinic_id])) {
    $clinicStr = "Clinic: " . $clinics[$clinic_id];
}
?>
<!--<p><strong><?php // echo $siteStr  ?>, <?php // echo $clinicStr  ?></strong>-->
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
                            <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $clinic_id ?>/"><?php echo $site->short_name ?></option>
                            <?php
                        } else {
                            ?>
                            <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $clinic_id ?>/" selected><?php echo $site->short_name ?></option>
                            <?php
                        }
                    }
                    ?>
                </select><br/><br/>
                Clinic <select id="urlList" onchange="window.location.href = this.value">
                    <option value="">- Please select -</option>
                    <?php
                    foreach ($clinics as $c_id => $clinic_name) {
                        if ($clinic_id != $c_id) {
                            ?>
                            <option value = "/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $c_id ?>/"><?php echo $clinic_name ?></option>
                            <?php
                        } else {
                            ?>
                            <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $c_id ?>/" selected><?php echo $clinic_name ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </p>

            <?php $this->renderPartial('//base/_messages'); ?>

            <div class="whiteBox">
                <?php
                $from = 1 + ($pagen * $items_per_page);
                $to = ($pagen + 1) * $items_per_page;
                if ($to > $total_items) {
                    $to = $total_items;
                }
                ?>
                <h3>Results for <?php echo $clinics[$clinic_id] ?> Clinic, <?php echo $sites[$site_id - 1]->name ?>. You are viewing patients <?php echo $from ?> to <?php echo $to ?>, of <?php echo $total_items ?></h3>

                <div id="patient-grid" class="grid-view">
                    <table class="items">
                        <thead>
                            <tr>
                                <th style="display: none" id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/0/<?php echo $site_id ?>/<?php echo $clinic_id ?>">CRN</a></th>
                                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/1/<?php echo $site_id ?>/<?php echo $clinic_id ?>">First Name</a></th>
                                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/2/<?php echo $site_id ?>/<?php echo $clinic_id ?>">Last Name</a></th>
                                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/3/<?php echo $site_id ?>/<?php echo $clinic_id ?>">Age</a></th>
                                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/4/<?php echo $site_id ?>/<?php echo $clinic_id ?>">S/B</a></th>
                                <!-- speciality-specific column headings: -->
                                <?php
                                $cols = $clinic->getColumnNames($clinics[$clinic_id]);
                                foreach ($cols as $col_index => $column) {
                                    ?>
                            <!--                                    <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php // echo $pagen      ?>/<?php // if ($sort_dir == 0) {      ?>1<?php // } else {      ?>0<?php // }      ?>/11/<?php // echo $site_id      ?>/<?php // echo $clinic_id      ?>"><?php // echo $column      ?></a></th>-->
                                    <th id="patient-grid_c0"><?php echo $column ?></th>
                                <?php }
                                ?>
                                <!-- end of speciality-specific column headings -->
                                <th id="patient-grid_c0"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/5/<?php echo $site_id ?>/<?php echo $clinic_id ?>">Follow Up</a></th>
                                <th id="patient-grid_c0" width="8%"><a href="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/6/<?php echo $site_id ?>/<?php echo $clinic_id ?>">Date</a></th>
    <!--										<th id="patient-grid_c5">Site</th>-->
                                <th id="patient-grid_c5" width="3%">5yr Risk</th>
                                <th id="patient-grid_c5" width="3%">Flag</th>
                                <th id="patient-grid_c5" width="3%">Reviewed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($dataProvider->getData() as $i => $result) {
                                ?>
                                <tr class="<?php if ($i % 2 == 0) { ?>even<?php } else { ?>odd<?php } ?>">
                                    <td style="display: none; vertical-align:middle"><?php echo $result->patient->id ?></td>
                                    <td style="vertical-align:middle"><?php echo $result->patient->contact->first_name ?></td>
                                    <td style="vertical-align:middle"><?php echo $result->patient->contact->last_name ?></td>
                                    <td style="vertical-align:middle"><?php echo $result->patient->getAge() ?></td>
                                    <td style="vertical-align:middle">
                                        <?php
                                        $firm_id = $result->seen_by_user_id;

                                        $firm = Firm::model()->findByPk($firm_id);
                                        if (isset($firm) && $firm->name) {
                                            echo $firm->name;
                                        }
                                        ?></td>
                                    <!-- speciality-specific column values: -->
                                    <?php
                                    $cols = $clinic->getColumnNames($clinics[$clinic_id]);
                                    foreach ($cols as $col_index => $column) {
                                        $value = $clinic->getColumnValue($result->patient->id, $clinics[$clinic_id], $column);
                                        ?>
                                        <td style="vertical-align:middle"><?php echo $clinic->formatTableData($clinics[$clinic_id], $column, $value) ?></td>
                                    <?php }
                                    ?>
                                    <!-- end of speciality-specific column values -->

                                    <td style="vertical-align:middle"><?php echo $result->follow_up ?></td>
                                    <td style="vertical-align:middle"><?php
                            if ($result->visit_date) {
                                $date = new DateTime($result->visit_date);
                                echo $date->format('Y-m-d');
                            }
                                    ?></td>
                                    <!--td style="vertical-align:middle">ODTC</td-->
                                    <td style="vertical-align:middle">0%<?php ?></td>
                                    <td style="vertical-align:middle"><input type="checkbox" name="flag" value="Bike" /></td>
                                    <td style="vertical-align:middle"><input type="checkbox" name="reviewed" value="Bike" /><?php // echo $result->nhs_num       ?></td>
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
                            <span class="otherPages"><a href="/virtualClinic/results/<?php echo $i + 1 ?>/<?php if ($sort_dir == 0) { ?>0<?php } else { ?>1<?php } ?>/<?php echo $sort_by ?>"><?php echo 1 + ($i * $items_per_page) ?> - <?php echo $to ?></a></span>
                        <?php } ?>
                    <?php } ?>
                </div>

            </div> <!-- .whiteBox -->

        </div>	<!-- .wideColumn -->

    </div><!-- .wrapTwo -->
    <script type="text/javascript">
        $('#patient-grid .items tr td').click(function() {
            // if the cell index is > 10, leave because we want to let users click the check boxes:
            if ($(this).parent().children().index($(this)) > 10) {
                return true;
            }
            //                                    window.location.href = '/patient/viewhosnum/'+$(this).parent().children(":first").html();
            window.location.href = '/<?php echo $clinics[$clinic_id] ?>VirtualClinic/default/view/'+$(this).parent().children(":first").html();
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
                <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site->id ?>/<?php echo $clinic_id ?>/"><?php echo $site->short_name ?></option>
            <?php } ?>
        </select>
        <select id="urlList" onchange="window.location.href = this.value">
            <option value="">- Please select -</option>
            <?php
            foreach ($clinics as $clinic_id => $clinic_name) {
                ?>
                <option value="/virtualClinic/results/<?php echo $pagen ?>/<?php if ($sort_dir == 0) { ?>1<?php } else { ?>0<?php } ?>/<?php echo $sort_by ?>/<?php echo $site_id ?>/<?php echo $clinic_id ?>/"><?php echo $clinic_name ?></option>
            <?php } ?>
        </select></p>
    <?php
}
?>
