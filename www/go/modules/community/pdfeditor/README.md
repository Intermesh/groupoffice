# Updating pdf.js

This module uses a custom fork of pdf.js maintained at:
**[https://github.com/Intermesh/pdf.js](https://github.com/Intermesh/pdf.js)**

Do **not** edit pdf.js files directly inside this project. All changes must be made in the fork and built there first.

---

## Steps

### 1. Clone and edit the fork

Work on the fork **outside** of the Group Office project directory:

```bash
git clone https://github.com/Intermesh/pdf.js.git
cd pdf.js
```

Make your changes inside the cloned fork.

### 2. Build pdf.js

Follow the build instructions in the fork's repository. After a successful build, the output should contain a `build/` directory structured something like this:

```
pdf.js/
├── build/
│   ├── build/
│   └── web/
```

### 3. Replace the contents in this module

Navigate to the `pdf.js` folder inside this module and replace its contents with the `build` and `web` directories from your build output.

Please commit the changes in the fork, any changes to the build files in Group Office can also be committed to Group Office.