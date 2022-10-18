When the component is installed, the files in the SQL updates folder (admin/sql/updates/mysql) are read and the name of the last file alphabetically is used to populate the component's version number. The latest version number of the component installed is stored in the #__schemas table of the database.

For the automatic update to execute the update SQL files for future versions, this value must be present the #__schemas table.

!!!!
For this reason, it is good practice to create a SQL update file for each version (even if it is empty or just has a comment). This way the #__schemas version will always match the component version.
!!!!
!!!!
Even if you don't require the use of database, you can add an empty file in admin/sql/updates/mysql/1.0.0.sql, just to initialize schema version. In future versions, if you plan to use database tables, the update can be executed automatically.
!!!!
