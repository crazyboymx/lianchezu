function aCatalog(cid, province, city){
	this.cid = cid;
	this.province = province;
	this.city = city;
}
function addOption(text, value, select2){
	var newOption = document.createElement('option');
	newOption.text = text;
	newOption.value = value;
	document.getElementById(select2).add(newOption);
}
function delOption(select2){
	var length = document.getElementById(select2).options.length;
	for(var i = length - 1; i >=0; i--){
		document.getElementById(select2).options[i] = null;
	}
}
function changeChild(value, Arrays, select2){
	var i;
	delOption(select2);
	addOption('请选择', '-1', select2);
	for(i = 0; i < Arrays.length; i++){
		if(Arrays[i].province == value){
			addOption(Arrays[i].city, Arrays[i].cid, select2);
		}
	}
}
function init(Arrays, select1){
	var text = "";
	var length = Arrays.length;
	for(var i = 0; i < length; i++){
		if(Arrays[i].province != text && Arrays[i].province != ''){
			text = Arrays[i].province;
			var newOption = document.createElement('option');
			newOption.text = text;
			newOption.value = text;
			document.getElementById(select1).add(newOption);
		}
	}
}