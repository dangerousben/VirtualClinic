Virtual Clinic
=============

This module defines an extensible clinic for other specialities to utilise.

It is a lightweight wrapper that uses reflection to class load other
speciality clinics.

To define a virtual clinic, the following steps must be performed:

* First, select which sub-speciality clinic is to be created. Create a folder
in modules named `[SubSpecialityName]VirtualClinic`, where
`[SubSpecialityName]` is the name of the sub-speciality from the `subspecialty`
table, removing all white space and special characters in the name taken from
the table;

* Now create, in the root of the directory, a PHP class file named 
`[SubSpecialityName]VirtualClinicModule`.

* The next step is to decide what data to display in the columns of the virtual
clinic. To do this, an array of data needs to be created in the module,
with the following rules:

    - the array is an array of `columnName => [data]` pairs where `[data]`
    defines the event type, class and field to read in. `[data]` is an array of
    `key : value` pairs, where `key` is defined each for `event_type`,
    `class_name` and `field`. `field` can either be a string, giving the name
    of a basic data type such as a `string` or an `int`; or an array,
    giving an object graph path of field names, the final one being a simple
    data type; or (finally) an array of arrays of any of the previous two types.

* Once the columns have been defined, create a controller to render the view
in `controllers/DefaultController.php`, and a view in `views/default/view.php`.
Since each clinic will want a specific view for a patient, the following
boiler plate code would pass the requested patient to the view:


        public function actionView($id) {
        if (!$this->patient = Patient::model()->find('id=:id', array(':id' => $id))) {
            throw new CHttpException(403, 'Invalid patient id.');
        }

        $this->renderPartial(
            'view', array(), false, true);
        }

* The next step is to decide if any columns need any custom formatting. By
default the element data is displayed in it's basic format. However, many
data types (like eye draw fields) might need extra formatting to sit it inside
the HTML table correctly. If any fields require extra markup, define a method
named `formatData` in `[SubSpecialityName]VirtualClinicModule.php`:

  ... where `$columnName` is the name of the column to format and `$data` is
  the actual data to be formatted.

The method should check to see if the column name is the required column
to be formatted, then format the data with appropriate markup and return
it. It should return `null` if no formatting is required.

For example, to format data for the `Eye` column so that the text is bold,
do the following within the `formatData` method:

    if ($columnName = 'Eye') {
        return "<b>" . $data . "</b>";
    }

As you can see this enables great flexibility with custom formatting for
each defined column.

Final tasks: don't forget to add the module to the YII config.