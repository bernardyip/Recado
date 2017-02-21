del SQLCombined.sql
for %%f in (*.sql) do type "%%f" >> SQLCombined.txt
move SQLCombined.txt SQLCombined.sql