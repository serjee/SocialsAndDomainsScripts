$(function(){

    $("#whoisfield").focus();
    
    $("#whoisfield").keyup(function(e) {
        if(e.keyCode == 13) $("#whoisbtn").trigger("enterPress");
    });
        
});

// function check name from another domains
function checkanother() {
    var dom = $("#whoisfield").val().replace(/^(http:\/\/)?(www\.)?([\/]+)?/gi, "");
    var arrDom = dom.replace("/", "").split(/[.]/);    
    $("#whoisfield").val(arrDom[0]);
    $("#whoisbtn").click();
}
    
// function enter new name to field
function enternewname() {
    $("#whoisfield").val("");
    $("#whoisfield").focus();
}