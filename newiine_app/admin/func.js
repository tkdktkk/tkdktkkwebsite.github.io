
function submitSetting() {
  var newpw = document.getElementById('newpw').value;
  var confirmPw = document.getElementById('confirm-pw').value;
  var banIP = document.getElementsByName('banIP')[0].value;

  if (newpw != '' && confirmPw == '') {
    alert('新パスワードは確認のため二度入力してください。');
    return false;
  } else if(newpw !== confirmPw) {
    alert('新パスワードが一致しません。再度入力してください。');
    return false;
  }
  
  // ipアドレスチェック
if (banIP != '' && banIP.match(/^\d{1,3}(\.\d{1,3}){3}$/) == null) {
  //ipアドレス以外
  alert("正しいIPアドレスを入力してください。");
  return false;
}

    var flag = confirm ( "設定を変更しますか？");
    return flag;
}

function submitSetting() {
  var newpw = document.getElementById('newpw').value;
  var confirmPw = document.getElementById('confirm-pw').value;
  var limitpost = document.getElementById('limitpost').value;
  var saveperiod = document.getElementById('saveperiod').value;

  if(limitpost <= 0) {
    alert('１日のいいね数上限は正の整数で設定してください。');
    return false;
  }

  if(saveperiod <= 0) {
    alert('いいねログデータの保存日数は正の整数で設定してください。');
    return false;
  }


  if (newpw != '' && confirmPw == '') {
    alert('新パスワードは確認のため二度入力してください。');
    return false;
  } else if(newpw !== confirmPw) {
    alert('新パスワードが一致しません。再度入力してください。');
    return false;
  }

    var flag = confirm ( "設定を変更しますか？");
    return flag;
}