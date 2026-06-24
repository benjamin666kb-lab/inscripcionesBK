import json
import re
import sys
import zipfile
import xml.etree.ElementTree as ET


NS = {
    "main": "http://schemas.openxmlformats.org/spreadsheetml/2006/main",
    "rel": "http://schemas.openxmlformats.org/package/2006/relationships",
}


def col_to_index(cell_ref):
    match = re.match(r"([A-Z]+)", cell_ref or "")
    if not match:
        return 0
    value = 0
    for char in match.group(1):
        value = value * 26 + ord(char) - 64
    return value - 1


def read_shared_strings(zf):
    if "xl/sharedStrings.xml" not in zf.namelist():
        return []
    root = ET.fromstring(zf.read("xl/sharedStrings.xml"))
    strings = []
    for item in root.findall("main:si", NS):
        parts = []
        for text in item.findall(".//main:t", NS):
            parts.append(text.text or "")
        strings.append("".join(parts))
    return strings


def cell_value(cell, shared_strings):
    cell_type = cell.attrib.get("t")
    value_node = cell.find("main:v", NS)
    inline_node = cell.find("main:is/main:t", NS)

    if cell_type == "inlineStr":
        parts = []
        for text in cell.findall(".//main:t", NS):
            parts.append(text.text or "")
        return "".join(parts)

    if value_node is None:
        return ""

    raw = value_node.text or ""
    if cell_type == "s":
        try:
            return shared_strings[int(raw)]
        except (ValueError, IndexError):
            return raw
    return raw


def workbook_sheets(zf):
    workbook = ET.fromstring(zf.read("xl/workbook.xml"))
    rels = ET.fromstring(zf.read("xl/_rels/workbook.xml.rels"))
    targets = {
        rel.attrib["Id"]: rel.attrib["Target"]
        for rel in rels.findall("rel:Relationship", NS)
    }
    sheets = []
    for sheet in workbook.findall("main:sheets/main:sheet", NS):
        rel_id = sheet.attrib.get("{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id")
        target = targets.get(rel_id, "")
        if not target.startswith("xl/"):
            target = "xl/" + target
        sheets.append({"name": sheet.attrib.get("name"), "path": target})
    return sheets


def inspect(path, max_rows=12):
    with zipfile.ZipFile(path) as zf:
        shared_strings = read_shared_strings(zf)
        result = []
        for sheet in workbook_sheets(zf):
            if sheet["path"] not in zf.namelist():
                continue
            root = ET.fromstring(zf.read(sheet["path"]))
            dimension = root.find("main:dimension", NS)
            rows = []
            for row in root.findall("main:sheetData/main:row", NS)[:max_rows]:
                values = []
                for cell in row.findall("main:c", NS):
                    index = col_to_index(cell.attrib.get("r"))
                    while len(values) < index:
                        values.append("")
                    values.append(cell_value(cell, shared_strings))
                rows.append(values)
            result.append({
                "sheet": sheet["name"],
                "dimension": dimension.attrib.get("ref") if dimension is not None else "",
                "rows": rows,
            })
        return result


if __name__ == "__main__":
    print(json.dumps(inspect(sys.argv[1]), ensure_ascii=False, indent=2))
