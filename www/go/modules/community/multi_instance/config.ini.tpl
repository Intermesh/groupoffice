[general]
; Where group-office stores files
dataPath = "{dataPath}"

; Temporary files path
tmpPath = "{tmpPath}"

; Enable debugging mode
debug = false

[db]
dsn = "mysql:host={dbHost};dbname={dbName}"
username = "{dbUsername}"
password = "{dbPassword}"

[limits]
; Limit the amount of users that may be created
userCount = 0

; Limit the amount of files data the installation can use. Use 1G, 100MB etc. 
storageQuota = 0 

; Limit the modules. Use comma separated string "email,addressbook,users,modules,groups"
allowedModules = ""
