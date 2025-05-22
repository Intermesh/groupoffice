import {btn, DefaultEntity, Menu, t} from "@intermesh/goui";
import {jmapds, User} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";

export class BookmarkContextMenu extends Menu {
	constructor(user: User, bookmark: DefaultEntity) {
		super();

		this.isDropdown = true;

		const writtenByUser = (user.id == bookmark.creator.id);

		this.items.add(
			btn({
				icon: "edit",
				text: t("Edit"),
				disabled: user.isAdmin ? false : !writtenByUser,
				handler: () => {
					if (user.isAdmin || writtenByUser) {
						const dlg = new BookmarksDialog();
						void dlg.load(bookmark.id);
						dlg.show();
					}
				}
			}),
			btn({
				icon: "delete",
				text: t("Delete"),
				disabled: user.isAdmin ? false : !writtenByUser,
				handler: () => {
					if (user.isAdmin || writtenByUser) {
						void jmapds("Bookmark").confirmDestroy([bookmark.id]);
					}
				}
			})
		)
	}
}