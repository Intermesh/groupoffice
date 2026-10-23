# ExtJS3 → GOUI port

The former ExtJS3 frontend (`views/extjs3`: Loader.js, EditorWindow.js, SystemSettingsPanel.js, Module.js) is
replaced by this GOUI bundle. The backend is unchanged.

Status: **browser** = verified in the running GO 26 instance (admin, spreadsheet server in Docker, no license key),
**types** = type-checked only.

| Area | Behaviour | Classification | Status |
|---|---|---|---|
| Registration | Module registered, system settings panel "Syncfusion" | port (`Index.ts`) | browser |
| Registration | Global `go.modules.community.syncfusion.openFile(fileId, fileName, extension, folderId)`, called by `filehandler/Syncfusion.php` | bridge (`Ext.ns`) | browser |
| Registration | Global `openBlob(blobId, fileName, listeners)` with ExtJS listener signature `save(win, blobId)`, used by amd/dms `DocumentDetail.js` | bridge (`Api.ts`) | browser (save listener) |
| Registration | Global `loadLibrary(editorType, callback)` | bridge (`Index.ts`) | types |
| Cross-module | "Edit with Syncfusion" in the legacy Files context menu, only for one selected supported file | bridge (`FilesContextMenu.ts`, prototype override as before) | browser |
| Cross-module | Files grid reloads after a save | bridge (`GO.mainLayout.getModulePanel`) | browser |
| Editor | Window 1000×800 (same size as `dp()`), maximizable, resizable, title "<file> - Syncfusion" | port | browser |
| Editor | Mask from show until the editor is ready | port | browser |
| Editor | Library loaded once into the page (CDN or `lib/`), license registered, failed load rejects and can be retried | port (`Loader.ts`) | browser (CDN) |
| Editor | Documents in an iframe with their own ej2 copy | port | browser (library load in iframe) |
| Editor | Locale JSON from jsdelivr for non-English users | port | types |
| Editor | Document editor: ribbon, File menu Save/Print, restrictEditing when read-only | port | browser (editable case, by the user against the remote document server) |
| Editor | Spreadsheet editor: open from JSON, File menu with only Save/Print, protected when read-only | port | browser (editable case) |
| Editor | Ctrl/Cmd+S saves | port | browser (spreadsheet) |
| Editor | Save: spreadsheet export → save to fileId or blob, `save` event | port | browser (fileId and blob) |
| Editor | Save: document → DOCX/TXT blob upload → save; doc/dotx/rtf create a new .docx and the window retargets to it | port | browser (save of an edited document); retarget: types |
| Editor | Backend errors shown in an error dialog, window closes when opening failed | port | browser ("Document service URL is not configured.") |
| Editor | Close with unsaved changes asks for confirmation | port | browser |
| Editor | Editor, iframe and resize observer released before the window leaves the DOM (the document editor writes to the iframe's storage on destroy) | port | browser (no error on close) |
| Settings | License key, library source (CDN/local), CDN URL, service URLs, service secret (password field) | port (`SystemSettings.ts`) | browser (load, save) |
| Settings | CDN URL hidden and lib/ hint shown for the local source | port | browser |
| Settings | Setup instructions | port | browser |

## Differences from the ExtJS version

- Ctrl/Cmd+S in the spreadsheet is captured before the spreadsheet's own handler. With a bubbling listener, as the
  ExtJS version registered it, the spreadsheet opened its built-in Save As dialog (seen in this port before the
  change), which posts the workbook from the browser to the Docker service.
- Error messages that were hard-coded English are translated (`Failed to load the Syncfusion library.`,
  `No file or blob specified.`, `Docker services`).
- The spreadsheet server image listens on port 8080; the setup instructions now map `6003:8080`.
- `openBlob()` also accepts a `close(win)` listener.
- A read-only spreadsheet also sets `allowEditing: false`; `isProtected` alone protects only the workbook structure, so
  cells could be edited but never saved.
- A second save is ignored while one is running (Ctrl+S is not blocked by the mask), so DMS gets one version per save.
- Closing the window while the library loads or the file converts stops the remaining steps.

## `as any`

`Loader.ts` and `EditorWindow.ts` read the `ej` global of the page or the iframe through `as any`: it comes from the
Syncfusion bundle loaded at runtime and has no type definitions here.

## Not verified

- Saving a doc/dotx/rtf file as a new .docx.
- Read-only files (`canEdit: false`), the local library source, a non-English locale.
- A non-admin user (the module grants Internal `mayRead` on install).
