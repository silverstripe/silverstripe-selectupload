(function () {
  function observeElement(element, property, callback, delay = 0) {
    let elementPrototype = Object.getPrototypeOf(element);
    if (elementPrototype.hasOwnProperty(property)) {
      let descriptor = Object.getOwnPropertyDescriptor(
        elementPrototype,
        property
      );
      Object.defineProperty(element, property, {
        get: function () {
          return descriptor.get.apply(this, arguments);
        },
        set: function () {
          let oldValue = this[property];
          descriptor.set.apply(this, arguments);
          let newValue = this[property];
          if (typeof callback == "function") {
            setTimeout(callback.bind(this, oldValue, newValue), delay);
          }
          return newValue;
        },
      });
    }
  }

  /**
   * Handle upload field change
   */
  const handleUploadFieldChange = (folderURL, folderID) => {
    if (folderID !== "") {
      const securityID = document.querySelector("input[name=SecurityID]").value;

      formData = new FormData();
      formData.append("FolderID", folderID);
      formData.append("SecurityID", securityID);
      let xmlhttp = new XMLHttpRequest();
      xmlhttp.open("POST", folderURL);
      xmlhttp.send(formData);
    }
  };

  /**
   * Handle folder select action when clicking the select folder button
   */
  const handleDisplayFolderSelect = (e) => {
    e.preventDefault();

    const field = e.target.closest(".selectupload");
    const folderSelectWrapper = field.querySelector(".select-folder-container");

    folderSelectWrapper.classList.remove("hide");

    field.querySelector(".ss-uploadfield-item-name small").innerHTML =
      "Select upload folder:";
  };

  /**
   * Attach event listeners
   */
  document.addEventListener("DOMContentLoaded", () => {
    console.log("attach");
    document.addEventListener("click", function (e) {
      if (
        e.target &&
        e.target.matches(
          ".ss-uploadfield-item .ss-uploadfield-item-info .ss-uploadfield-item-name *"
        )
      ) {
        handleDisplayFolderSelect(e);

        const folders = document.querySelectorAll(
          ".selectupload div.folderdropdown input[type=hidden]"
        );

        folders.forEach((elem) => {
          observeElement(elem, "value", (old, newValue) => {
            const folderURL = elem
              .closest(".selectupload")
              .querySelector("[data-folder-link]")
              .getAttribute("data-folder-link");
            console.log("changed to ", newValue);
            handleUploadFieldChange(folderURL, newValue);
          });
        });
      }
    });
  });
})();
