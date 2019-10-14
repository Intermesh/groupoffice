General
#######

1. Use tabs to indent code
2. Use braces with all structures
3. Don't use ?> close tag at the end of class files
4. One file per class.
5. YAGNI

Naming conventions
##################

Properties: lowerCamelCase
Methods: lowerCamelCase
Constants: UPPER_UNDERSCORED
Database tables: lower_underscored (For windows compatibility)
Namespaces: lower_underscored

Group-Office version number
###########################

y.x.z (eg. 6.3.1)

y: Major version number
- Major new features or breaking API changes.
- Custom modules may break. 
- Chronologic upgrade is required. You can't skip versions between upgrades.
- Pro license needs to be renewed

x:
- New features but no breaking changes
- You can skip versions between upgrades

z:
- Bug fixes only
- You can skip versions between upgrades.
	

