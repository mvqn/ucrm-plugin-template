


let prefix = "";
let version = "";

if(window.location.pathname.startsWith("/crm"))
{
    let element = window.parent.document.getElementById("header-version__version");

    prefix = "/crm";
    version = element ? "?v=" + element.innerText : "";

    // UNMS v1.X to UCRM v3.X
    version = version.replace("?v=1.", "?v=3.");
}
else
{
    let elements = document.getElementsByClassName("appGlobalHeader__versionItem");
    let element = elements && elements.length > 0 ? elements[0] : null;

    prefix = "";
    version = element ? "?v=" + element.getAttribute("data-clipboard-text") : "";
}


//document.write('<base href="' + prefix + '<%= BASE_URL %>">');


//console.log(prefix);
//console.log(version);

document.write(
    '<!--suppress SpellCheckingInspection -->' +
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/assets/fonts/lato/lato.css' + version + '">');
document.write(
    '<!--suppress SpellCheckingInspection -->' +
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/assets/fonts/ubnt-icon/ubnt-icon.css' + version + '">');
document.write(
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/assets/fonts/ucrm-icon/style.css' + version + '">');
document.write(
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/assets/vendor/jquery-ui/themes/smoothness/jquery-ui.min.css' + version + '">');
document.write(
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/assets/vendor/leaflet/leaflet.css' + version + '">');
document.write(
    '<link rel="stylesheet" ' +
          'href="' + prefix + '/dist/main.min.css' + version + '">');

