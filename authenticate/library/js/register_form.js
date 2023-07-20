$(document).ready(function () {
    var aupAcceptButtonElem = $("button#btn-aup-accept"), aupAcceptButtonIconElem = aupAcceptButtonElem.find("i"),
        aupAcceptInputElem = $("input[name=\"acceptable_use_policy\"]");
    
    aupAcceptButtonElem.click(function (event) {
        if (aupAcceptInputElem.val() === "1") {
            aupAcceptButtonElem.attr("class", "btn btn-small btn-danger");
            aupAcceptButtonIconElem.attr("class", "fa fa-fw fa-times");
            
            aupAcceptInputElem.val("");
        } else {
            aupAcceptButtonElem.attr("class", "btn btn-small btn-success");
            aupAcceptButtonIconElem.attr("class", "fa fa-fw fa-check");
            
            aupAcceptInputElem.val(1);
        }
    });
});
