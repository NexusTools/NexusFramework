ControlPanel.importTableData = function(){
    if(!("FormData" in window) || !("XMLHttpRequest" in window)) {
        alert("Your browser is too old to support this feature.");
        throw "Death";
    }
    
    var formData = new FormData();
        
    ControlPanel.page.whiteout.popup.select("select, input[type=hidden]").each(function(e){
        formData.append(e.readAttribute("name"), e.value);
    });
    
    ControlPanel.page.whiteout.popup.select("input[type=radio]").each(function(e){
        if(!e.checked)
            return;
        formData.append(e.readAttribute("name"), e.value);
    });
    
    ControlPanel.page.whiteout.popup.select("input[type=file]").each(function(e){
        if(!("files" in e)) {
            alert("Your browser is too old to support this feature.");
            throw "Death";
        }
        if(e.files.length == 0) {
            alert("Select a File");
            throw "Death";
        }
        formData.append(e.readAttribute("name"), e.files[0]);
    });
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '{{BASE_URI}}control/Database/import-insecure', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                if(xhr.status == 200) {
                    try {
                        var data = eval(xhr.responseText);
                        if("text" in data) {
                        	var text = data.text;
                        	if(data['error-messages'].length) {
                        		text += "<br /><hr /><b>First Error Message</b>";
                    			text += "<br />" + data['error-messages'][0][0][2];
                        	}
                            ControlPanel.alertDialog(text, "Import Complete");
                            return;
                        }
                    }catch(err){
                        console.log(err);
                        throw "Response Corrupt";
                    }
                    if("error" in data)
                        throw data.error;
                    throw "Response Corrupt";
                } else
                    throw "Server Inaccessible";
            }catch(err){
                ControlPanel.alertDialog("An internal error occured,<br />the table might be corrupt.<br /><br />" + err, "Import Failed");
            }
        }
    };

    xhr.send(formData);
    ControlPanel.popupOpen = false;
    ControlPanel.page.whiteout.popup.removeClassName("open");
}
