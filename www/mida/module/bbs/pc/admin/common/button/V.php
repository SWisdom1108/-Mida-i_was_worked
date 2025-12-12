<div class="bbsBtnWrap">
	<div class="left">
		<a href="<?=$_SESSION['listURL']?>"><i class="fas fa-arrow-left"></i>이전</a>
	</div>
	<div class="right">
		<a href="#" class="bbsDeleteBtn" data-bbs="<?=$bbsCode?>" data-idx='<?=$_GET['idx']?>'><i class="fas fa-trash-alt"></i>삭제</a>
		<a href="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=U&idx=<?=$_GET['idx']?>" class="typeMain"><i class="fas fa-pencil-alt"></i>수정</a>
	</div>
</div>