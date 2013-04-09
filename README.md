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
    defines the event type, class and field to read in;
    - the order the columns are specified is the order they will be displayed
    within the clinic;
    - the name of the array must be `columns`;
    - `[data]` is an array of
    `key : value` pairs, where `key` is defined each for `event_type`,
    `class_name` and `field`;
    - 'event_type` details the event type where the class is to be loaded from;
    - `class_name` is the name of the class to be loaded;
    -  `field` can either be a string, giving the name of a class member
    for a basic data type such as a `string` or an `int`, for the given
    class name. For example,
    `'field' => 'description'` would attempt to load the member `description`;
    or an array, giving an object graph path of field names, the final
    one being a simple data type. For example,
    `'field' => array('left_reading', 'value')` describes a simple data type
    member named `value` that is a member of the object `left_reading` for
    the current class being loaded; or (finally) an array of arrays of
    a mix of any of the previous two types (more on this below);
    - if `field` is an array, the number of elements in the array will be
    the number of elements returned to populate the column - this is important
    to remember if defining your own formatter for columns (again discussed
    below).

Let's consider an example. Say we want to display information about IOPs,
history, cup-to-disk ratio and conclusion, all from the examination event.
Then we could define this information as follows within the module:

    public $columns = array('IOP' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_IntraocularPressure',
            'field' => array(array('left_reading', 'value'), array('right_reading', 'value'))),
        'History' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_History',
            'field' => 'description'),
        'Conclusion' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_Conclusion',
            'field' => 'description'),
        'C/D Ratio' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_PosteriorSegment',
            'field' => array(array('left_cd_ratio', 'name'), array('right_cd_ratio', 'name'))));

Here we define four columns, named IOP, History, Conclusion and C/D Ratio.
For each, `event_type` is the same although clinics can access different
event types for different columns if required. Note how each column uses
a different class name and how each field is related directly to the class
being loaded. For IOP, we're interested in two values, one each for the left
and right eye. Since we're interested in two values, the `field` for IOP
is going to be an array. Within that array, we're not interested in fields
held directly within the IOP class - instead we're interested in the `value`
of the `left_reading` and `right_reading` objects. Thus,
`array('left_reading', 'value')` describes the object graph from the IOP class
through to the end value required. History and Conclusion are both simple
attributes of each respective class and are therefore easy to define.
Finally, the cup-to-disk ratios are in the same format as the IOP - they
are nested object graphs, describing the objects held within the posterior
segment object, each the the value `name`.

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

The view will need to be supported by a model in `models` if necessary.

* The next step is to decide if any columns need any custom formatting. By
default the element data is displayed in it's basic format. However, many
data types (like eye draw fields) might need extra formatting to sit it inside
the HTML table correctly. If any fields require extra markup, define a method
named `formatData` in `[SubSpecialityName]VirtualClinicModule.php`:

    public static function formatData($columnName, $data)

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

Example Clinic
==============

Here we discuss briefly how to create a sample clinic, using the above
examples.

By default we need to create a clinic that is named as a sub-speciality
in the database; here we choose glaucoma. The, we need to create a directory
in the `modules` directory named `GlaucomaVirtualClinic`.

Within that directory we need to create a PHP file named
`GlaucomaVirtualClinicModule.php`.