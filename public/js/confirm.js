function deleteHandler(event){
  // 一旦フォームの送信を止める
  event.preventDefault();
  if (window.confirm('本当に削除しますか？')){
    // okなら削除
    document.getElementById('delete-form').submit();
  }
  else {
    alert('削除をキャンセルしました');
  }
}