<?php

class SummaryController extends BaseEventTypeController {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/main';

    public function actionCreate() {
        parent::actionCreate();
    }

    public function actionUpdate($id) {
        parent::actionUpdate($id);
    }

    public function actionView($id) {
        if (!$this->patient = Patient::model()->find('id=:id', array(':id' => $id))) {
            throw new CHttpException(403, 'Invalid patient id.');
        }

        $this->renderPartial(
                'view', array(
                ), false, true);
    }

}
