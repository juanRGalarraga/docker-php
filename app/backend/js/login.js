var rulo = new rbtRulo(),
	evalResult = new rbtEvalResult();

document.addEventListener("DOMContentLoaded", function () {
    let frm = document.getElementById('loginform');
    frm.addEventListener('keydown', function (e) {
        var code = e.keyCode || e.which;
        if(code == 13) {
            CheckLogin();
        }	
    });
});
    
function CheckLogin() {
    var result = true,
        frm = document.getElementById('loginform');

    if (result) {
        getAjax({
            archivo: 'checkLogin',
            username: frm.username.value,
            password: frm.password.value
        }, function (a,b,c,d,e) {
            if (!e) {
                // window.location.reload();
            }
        });
    }
}


function Viewpassword(hijo){
    let inpt = document.querySelector("input[name=password]");
    if(inpt.getAttribute("type") == "password"){
        if(hijo.children[0] != undefined){
            hijo.children[0].setAttribute("class", "fa fa-eye-slash");
        }
        inpt.setAttribute("type","text");
    }else{
        inpt.setAttribute("type","password");
        if(hijo.children[0] != undefined){
            hijo.children[0].setAttribute("class", "fa fa-eye");
        }
    }
}