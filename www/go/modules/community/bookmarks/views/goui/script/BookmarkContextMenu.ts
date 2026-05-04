import {btn, DefaultEntity, Menu, t, Window} from "@intermesh/goui";
import {AclLevel, jmapds, User} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";

export class BookmarkContextMenu extends Menu {
	constructor(bookmark: DefaultEntity) {
		super();

		this.isDropdown = true;

		this.items.add(
			btn({
				icon: "edit",
				text: t("Edit"),
				disabled: bookmark.permissionLevel < AclLevel.WRITE,
				handler: () => {
					const dlg = new BookmarksDialog();
					void dlg.load(bookmark.id);
					dlg.show();
				}
			}),
			btn({
				icon: "delete",
				text: t("Delete"),
				disabled: bookmark.permissionLevel < AclLevel.DELETE,
				handler: () => {
					jmapds("Bookmark").confirmDestroy([bookmark.id]).catch(e => Window.error(e))
				}
			})
		)
	}
}