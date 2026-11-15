#!/usr/bin/env python3
"""Extract Parquet rows into a stable JSONL contract.

The EEA schema has controlled vocabulary fields and can evolve. This script inspects
column names and supports common field aliases, but must be verified against the
downloaded Bulgarian Parquet files before production use.
"""
import json, sys, zipfile, tempfile
from pathlib import Path
import pyarrow.parquet as pq

archive, output = sys.argv[1], sys.argv[2]
aliases = {
    "station_local_id": ["Samplingpoint", "samplingPoint", "localId", "SamplingPoint", "sampling_point"],
    "measured_at": ["DatetimeBegin", "datetimeBegin", "dateTimeBegin", "datetime_begin"],
    "value": ["Concentration", "value", "Value"],
    "unit": ["Unit", "unit"],
    "validity": ["Validity", "validity"],
    "verification": ["Verification", "verification"],
}

def choose(names, cols):
    return next((c for c in cols if c.lower() in {n.lower() for n in names}), None)

with tempfile.TemporaryDirectory() as tmp:
    with zipfile.ZipFile(archive) as z: z.extractall(tmp)
    parquet_files = list(Path(tmp).rglob("*.parquet"))
    if not parquet_files: raise SystemExit("No Parquet files found in EEA ZIP")
    with open(output, "w") as out:
        for file in parquet_files:
            table = pq.read_table(file)
            cols = table.column_names
            mapping = {key: choose(names, cols) for key,names in aliases.items()}
            missing = [k for k,v in mapping.items() if k in ("station_local_id","measured_at","value") and not v]
            if missing:
                raise SystemExit(f"Unrecognized EEA Parquet schema in {file.name}; columns={cols}; missing={missing}")
            df = table.select([v for v in mapping.values() if v]).to_pydict()
            n = table.num_rows
            for i in range(n):
                row = {}
                for key,col in mapping.items():
                    if col: row[key] = df[col][i]
                if row.get("measured_at") is not None:
                    row["measured_at"] = str(row["measured_at"])
                if row.get("value") is None: continue
                row["dataset"] = None
                out.write(json.dumps(row, default=str) + "\n")
