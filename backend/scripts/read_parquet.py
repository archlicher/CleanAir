#!/usr/bin/env python3
"""Extract EEA Parquet rows into the JSONL contract consumed by MeasurementImporter.

Verified against the live EEA download API (host eeadmz1-downloads-api-appservice) for
Bulgarian PM2.5 (E2a) files, which expose these columns:

    Samplingpoint   e.g. "BG/SPO-BG0045A_06001_100"
    Start           hourly period begin (timestamp)
    Value           concentration (decimal128)
    Unit            e.g. "ug.m-3"
    Validity        1 = valid, -1 = invalid
    Verification    1 = verified
    Pollutant       numeric pollutant code (6001 = PM2.5)

`station_local_id` is normalised to the EEA Air Quality Station EoI code
("SPO-BG0045A_06001_100" -> "BG0045A") so it matches the station metadata imported by
`air-quality:import-stations`.
"""
import json, re, sys, zipfile, tempfile
from datetime import date, datetime
from decimal import Decimal
from pathlib import Path
import pyarrow.parquet as pq

archive, output = sys.argv[1], sys.argv[2]

aliases = {
    "sampling_point": ["Samplingpoint", "samplingPoint", "SamplingPoint", "sampling_point", "localId"],
    "measured_at": ["Start", "DatetimeBegin", "datetimeBegin", "dateTimeBegin", "datetime_begin"],
    "value": ["Value", "Concentration", "value"],
    "unit": ["Unit", "unit"],
    "validity": ["Validity", "validity"],
    "verification": ["Verification", "verification"],
    "pollutant": ["Pollutant", "pollutant"],
}

# "BG/SPO-BG0045A_06001_100" -> country prefix + SPO- marker + EoI code + pollutant/agg suffix
SAMPLING_POINT = re.compile(r"^(?:[A-Z]{2}/)?(?:SPO-)?(?P<eoi>[^_]+)(?:_\d+_\d+)?$")


def choose(names, cols):
    lowered = {name.lower() for name in names}
    return next((col for col in cols if col.lower() in lowered), None)


def eoi_code(sampling_point):
    match = SAMPLING_POINT.match(str(sampling_point))
    return match.group("eoi") if match else str(sampling_point)


def scalar(value):
    if isinstance(value, Decimal):
        return float(value)
    if isinstance(value, (datetime, date)):
        return value.isoformat()
    return value


def dataset_from_path(path):
    parts = {part.upper() for part in path.parts}
    if "E2A" in parts:
        return 2
    if "E1A" in parts:
        return 1
    return None


with tempfile.TemporaryDirectory() as tmp:
    with zipfile.ZipFile(archive) as z:
        z.extractall(tmp)
    parquet_files = list(Path(tmp).rglob("*.parquet"))
    if not parquet_files:
        raise SystemExit("No Parquet files found in EEA ZIP")
    with open(output, "w") as out:
        for file in parquet_files:
            table = pq.read_table(file)
            mapping = {key: choose(names, table.column_names) for key, names in aliases.items()}
            missing = [key for key in ("sampling_point", "measured_at", "value") if not mapping[key]]
            if missing:
                raise SystemExit(
                    f"Unrecognized EEA Parquet schema in {file.name}; "
                    f"columns={table.column_names}; missing={missing}"
                )
            columns = table.select([col for col in mapping.values() if col]).to_pydict()
            dataset = dataset_from_path(file)
            for index in range(table.num_rows):
                raw = {key: columns[col][index] for key, col in mapping.items() if col}
                if raw.get("measured_at") is None or raw.get("value") is None:
                    continue
                row = {
                    "station_local_id": eoi_code(raw["sampling_point"]),
                    "sampling_point": raw["sampling_point"],
                    "measured_at": scalar(raw["measured_at"]),
                    "value": scalar(raw["value"]),
                    "unit": raw.get("unit"),
                    "validity": raw.get("validity"),
                    "verification": raw.get("verification"),
                    "pollutant_code": raw.get("pollutant"),
                    "dataset": dataset,
                }
                out.write(json.dumps(row, default=str) + "\n")
