此處每一個資料夾(folder)都代表一個data query的一項，安裝步驟如下：

1.如果<cacti_path>內沒有expertos資料夾的話，先創建一個expertos資料夾，有資料夾的話就跳過

2.將整個folder丟入expertos資料夾中

3.將folder內config.xml搬出來至expertos資料夾，因此expertos的資料結構為
	expertos
		project1
			cacti_file
			lib
			project1_d.py
			README.txt
		project2
			cacti_file
			lib
			project2_d.py
			README.txt

		project1_config.xml
		project2_config.xml

4.調整config.xml，請根據每一個folder中的README.txt修改，並且完成README.txt中的步驟

5.將folder中cacti_file內的檔案搬去cacti的指定位置(resources、script、template)

6.在cacti web GUI中使用data query的方式建圖
	