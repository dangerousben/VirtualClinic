<?php

class DefaultController extends BaseEventTypeController {

    public function actionCreate() {
        parent::actionCreate();
    }
    
    /**
     * Creates necessary elements, as well as creating the visit date (based
     * on the event date time) and seen by user ID (of the currently logged
     * on user) for the clinic.
     * 
     * @param type $elements
     * @param type $data
     * @param type $firm
     * @param type $patientId
     * @param type $userId
     * @param type $eventTypeId
     * @return Event event id if the creation was successful; false otherwise.
     */
    public function createElements($elements, $data, $firm, $patientId, $userId, $eventTypeId) {
        $event_id = parent::createElements($elements, $data, $firm, $patientId, $userId, $eventTypeId);
        $event = Event::model()->findByPk($event_id);
        VirtualClinicController::updateClinic($event->episode->patient->id,
                array( 
            'visit_date' => $event->datetime, 'seen_by_user_id' => Yii::app()->user->id));
        return $event_id;
    }

    /**
     * Updates elements as per the parent class; also updates the clinic
     * with the visit date (of the event) and the seen my user id as the
     * currently logged on user.
     * 
     * @param type $elements
     * @param type $data
     * @param Event $event
     * @return boolean true if the update was successful, false otherwise.
     */
    public function updateElements($elements, $data, $event) {
        $success = parent::updateElements($elements, $data, $event);
        VirtualClinicController::updateClinic($event->episode->patient->id,
                array( 
            'visit_date' => $event->datetime, 'seen_by_user_id' => Yii::app()->user->id));
        return $success;
    }
    
    public function actionUpdate($id) {
        parent::actionUpdate($id);
    }

    public function actionView($id) {
        parent::actionView($id);
    }

}
