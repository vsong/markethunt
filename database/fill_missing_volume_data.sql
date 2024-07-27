-- Fills in missing volume data for a single day using weekly volume data.
-- The missing date must be >= start date and < end date.
-- The start date and end date must span 7 days inclusive.
-- The missing date must be the only date within the range that has missing daily volume data.
-- The end date must have a value for weekly volume.

insert
	into
	daily_volume (item_id,
	`date`,
	raw_volume_day,
	raw_volume_week,
	raw_volume_month)
with existing_item_ids as (
	select
		item_id
	from
		daily_volume
	where
		`date` = 'MISSING_DATE'
),
	sums as (
	select
		item_id,
		sum(raw_volume_day) as sum
	from
		daily_volume
	where
		`date` >= 'START_DATE'
		and `date` <= 'END_DATE'
	group by
		item_id
)
select
	item_id,
	date('MISSING_DATE') as `date`,
	raw_volume_week - sum as raw_volume_day,
	null as raw_volume_week,
	null as raw_volume_month
from
	daily_volume dv
left join sums
		using (item_id)
where
	`date` = 'END_DATE'
	and item_id not in (
	select
		*
	from
		existing_item_ids)
;