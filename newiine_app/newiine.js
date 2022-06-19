jQuery(function() {
  'use strict';

  var newiineMessageVisibleTime = 6000;
  // お礼メッセージを表示する時間の長さを変更できます（単位はミリ秒。6000＝6秒）

  // ------------------------------------------

  // ここから下は基本的にいじらないでください

  // ------------------------------------------

  // 自分の設置されているURLの取得
  var root;
  var scripts = document.getElementsByTagName("script");
  var i = scripts.length;
  while (i--) {
      var match = scripts[i].src.match(/(^|.*\/)newiine\.js$/);
      if (match) {
          root = match[1];
          break;
      }
  }

  var ajaxPath = root+'_ajax.php';

  var newIinePathname = location.href;

  var iineItemButton = [];
  var iineItemButtonArray = [];
  var iineItemButtonName = [];
  var iineItemButtonCount = [];
  var iineItemButton = document.getElementsByClassName('newiine_btn');
  var iineItemThanksMessage = [];

  for (var i = 0; i < iineItemButton.length; i++) {
    iineItemButtonArray[i] =  iineItemButton[i];
    iineItemButtonName[i] = iineItemButton[i].dataset.iinename;
    if(iineItemButton[i].getElementsByClassName('newiine_count')[0] !== undefined) {
        iineItemButtonCount[i] = iineItemButton[i].getElementsByClassName('newiine_count')[0];
    } else {
        iineItemButtonCount[i] = null;
    }

    if(iineItemButton[i].getElementsByClassName('newiine_thanks')[0] !== undefined) {
      iineItemThanksMessage[i] = iineItemButton[i].getElementsByClassName('newiine_thanks')[0];
    } else {
      iineItemThanksMessage[i] = null;
    }
  }

  const targets = iineItemButtonArray;

  var newiineUpdateCount = function(h, res) {
    if(iineItemButtonCount[h] !== null) {
      iineItemButtonCount[h].innerHTML = res;
    }
  }

  var newiineFadeout = function(e) {
    setTimeout(function(){
      e.classList.add('newiine_fadeout');
    }, newiineMessageVisibleTime);
    setTimeout(function(){
      e.style.display = "none";
      e.classList.remove('newiine_fadeout');
    }, newiineMessageVisibleTime + 1000);
   }

  targets.forEach(function(target, h) {
    jQuery.ajax({
        type: 'GET',
        url : ajaxPath,
        data:{ buttonname: iineItemButtonName[h] }
      }).fail(function(){
        alert('お使いのサーバーでPHPが利用できるか確認して下さい。');
      }).done(function(res){
        var data_arr = JSON.parse(res); //戻り値をJSONとして解析
        newiineUpdateCount(h, data_arr[0]);
        if(data_arr[1] == true) {
          iineItemButtonArray[h].classList.add('newiine_clickedtoday');
        }
        });

      //クリックしたときの処理
    target.addEventListener('click', function(e) {
        e.preventDefault();

        if(typeof target.dataset.iineurl !== "undefined"){
          newIinePathname = target.dataset.iineurl;
        }

        var iineNewCountLimit;
        if(target.dataset.iinecountlimit > 0){
          iineNewCountLimit = target.dataset.iinecountlimit;
        } else {
          iineNewCountLimit = false;
        }
    
        // ajax処理
        jQuery.post(ajaxPath, {
          path: newIinePathname,
          buttonname: iineItemButtonName[h],
          iineNewCountLimit: iineNewCountLimit,
          mode: 'check'
        }).fail(function(){
          alert('何かが上手くいかなかったようです。');
        }).done(function(res){
          if(res === 'upper') {
            console.log(res);
          } else if(res === 'denyIP') {
            console.log(res);
          } else if(res === 'else') {
            console.log(res);
          } else {
            console.log(res);
            var data_arr = JSON.parse(res); //戻り値をJSONとして解析
            newiineUpdateCount(h, data_arr[0]);
            // アニメーション
            iineItemButtonArray[h].classList.remove('newiine_animate');
            iineItemButtonArray[h].classList.add('newiine_animate');
            setTimeout(function(){
              iineItemButtonArray[h].classList.remove('newiine_animate');
            },500);
            if(iineItemThanksMessage[h] !== null) {
              iineItemThanksMessage[h].style.display = "block";
              newiineFadeout(iineItemThanksMessage[h]);
            }
            
            iineItemButtonArray[h].classList.add('newiine_clicked');
            var bros = [];
            for (var i = 0; i < iineItemButton.length; i++) {
              if(iineItemButtonName[i] === iineItemButtonName[h] && i !== h) {
                  bros.push(i);
              }
            }
            if(bros.length > 0) {
              bros.forEach((e) => {
                newiineUpdateCount(e, data_arr[0]);
                iineItemButtonArray[e].classList.add('newiine_clicked');
              });
            }
          }
        });
    });
});

});