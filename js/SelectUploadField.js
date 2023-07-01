jQuery.entwine("selectupload", function ($) {

  $(".field.selectupload div.folderdropdown input[type=hidden]").entwine({
    onchange: function () {
      let folderID = $(this).val();
      if (folderID !== '') {
        const securityID = document.getElementById("Form_EditForm_SecurityID")
          ? document.getElementById("Form_EditForm_SecurityID").value
          : document.getElementById("Form_ItemEditForm_SecurityID").value;

        let formData = new FormData();
        formData.append('FolderID', folderID);
        formData.append('SecurityID', securityID);
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.open("POST", folderURL);
        xmlhttp.send(formData);
      }
    }
  });

  $(".field.selectupload .ss-uploadfield-item .ss-uploadfield-item-info .ss-uploadfield-item-name .change-folder").entwine({
    onclick: function () {
      const folderSelectWrapper = $(this).parents('.ss-uploadfield-item-info').find(".select-folder-container");
      folderSelectWrapper.removeClass("hide");
      $(this).parents('.ss-uploadfield-item-info').find(".ss-uploadfield-item-name small").html("Select upload folder:");
    }
  });

});
