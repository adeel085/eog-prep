/**
 * @license Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * CKEditor 4 LTS ("Long Term Support") is available under the terms of the Extended Support Model.
 */
CKEDITOR.dialog.add("semediccustomtextfield", function (editor) {
    var acceptedTypes = {
        email: 1,
        password: 1,
        search: 1,
        tel: 1,
        text: 1,
        url: 1,
    };

    function autoCommit(data) {
        var element = data.element;
        var value = this.getValue();

        value
            ? element.setAttribute(this.id, value)
            : element.removeAttribute(this.id);
    }

    function autoSetup(element) {
        var value =
            element.hasAttribute(this.id) && element.getAttribute(this.id);
        this.setValue(value || "");
    }

    return {
        title: editor.lang.forms.textfield.title,
        minWidth: 350,
        minHeight: 150,
        getModel: function (editor) {
            var element = editor.getSelection().getSelectedElement();

            if (
                element &&
                element.getName() == "input" &&
                (acceptedTypes[element.getAttribute("type")] ||
                    !element.getAttribute("type"))
            ) {
                return element;
            }

            return null;
        },
        onShow: function () {
            var element = this.getModel(this.getParentEditor());
            if (element) {
                this.setupContent(element);
            }
        },
        onOk: function () {
            var editor = this.getParentEditor(),
                element = this.getModel(editor),
                isInsertMode =
                    this.getMode(editor) == CKEDITOR.dialog.CREATION_MODE;

            if (isInsertMode) {
                element = editor.document.createElement("input");
                element.setAttribute("type", "text");
            }

            var data = { element: element };

            if (isInsertMode) {
                editor.insertElement(data.element);
            }

            this.commitContent(data);

            // Element might be replaced by commitment.
            if (!isInsertMode)
                editor.getSelection().selectElement(data.element);
        },
        onLoad: function () {
            this.foreach(function (contentObj) {
                if (contentObj.getValue) {
                    if (!contentObj.setup) contentObj.setup = autoSetup;
                    if (!contentObj.commit) contentObj.commit = autoCommit;
                }
            });
        },
        contents: [
            {
                id: "info",
                label: editor.lang.forms.textfield.title,
                title: editor.lang.forms.textfield.title,
                elements: [
                    {
                        id: "type",
                        type: "select",
                        label: editor.lang.forms.textfield.type,
                        default: "patientName",
                        accessKey: "M",
                        items: [
                            ["Nombre del paciente", "patientName"],
                            ["Paciente DNI", "patientDni"],
                            ["Fecha de nacimiento del paciente", "patientDob"],
                            ["Nombre del médico", "doctorName"],
                            ["Médico DNI", "doctorDni"],
                            ["Doctor Colegiado Número", "collegiateNumber"],
                            ["Fecha actual", "currentDate"],
                            ["Campo libre", "freeTextField"],
                        ],
                        setup: function (element) {
                            this.setValue(element.getAttribute("type"));
                        },
                        commit: function (data) {
                            var element = data.element;

                            if (CKEDITOR.env.ie) {
                                var elementType = element.getAttribute("type");
                                var myType = this.getValue();

                                if (elementType != myType) {
                                    var replace =
                                        CKEDITOR.dom.element.createFromHtml(
                                            '<input class="mycustomtextfield" type="text" name="' +
                                                myType +
                                                '"></input>',
                                            editor.document
                                        );
                                    element.copyAttributes(replace, {
                                        type: 1,
                                    });
                                    replace.replace(element);
                                    data.element = replace;
                                }
                            } else {
                                element.setAttribute("type", "text");
                                element.setAttribute("name", this.getValue());
                                element.setAttribute(
                                    "class",
                                    "mycustomtextfield"
                                );
                            }
                        },
                    },
                ],
            },
        ],
    };
});
