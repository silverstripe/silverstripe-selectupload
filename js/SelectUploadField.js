const uploadFields = document.querySelectorAll(".selectupload div.folderdropdown input[type=hidden]");
const changeFolderLink = document.querySelector(".ss-uploadfield-item .ss-uploadfield-item-info .ss-uploadfield-item-name .change-folder");
const handleUploadFieldChange = (event) => {
    let folderID = event.target.value;
    if (folderID !== '') {
        const securityID = document.getElementById("Form_EditForm_SecurityID").value;

        formData = new FormData();
        formData.append('FolderID', folderID);
        formData.append('SecurityID', securityID);
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.open("POST", folderURL);
        xmlhttp.send(formData);
    }
};

const handleDisplayFolderSelect = () => {
    const folderSelectWrapper = document.querySelector(".ss-uploadfield-item .select-folder-container");
    folderSelectWrapper.classList.remove("hide");
    document.querySelector(".ss-uploadfield-item .ss-uploadfield-item-info .ss-uploadfield-item-name small").innerHTML =
    "Select upload folder:";
}

changeFolderLink.addEventListener('click', handleDisplayFolderSelect);
uploadFields.forEach(function (field) {
    document.getElementById(field.id).onchange = handleUploadFieldChange;
});
