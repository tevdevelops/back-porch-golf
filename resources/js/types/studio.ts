export type Studio = {
    timezone: string;
    rate_cents: number;
    extra_player_cents: number;
    tax_rate: number;
    max_players: number;
    slot_minutes: number;
    min_minutes: number;
    max_minutes: number;
    cancel_cutoff_hours: number;
    door_code_lead_minutes: number;
};

export type Flash = {
    notice: string | null;
    door: { unlocked: boolean; message: string; at: string } | null;
};

export type Bay = { id: number; name: string };

export type GridStatus = 'Booked' | 'In Progress' | 'Complete';

/** A booking on the grid, in minutes from the shown day's local midnight. */
export type Block = {
    bay_id: number;
    offset: number;
    minutes: number;
    starts_at: string;
    ends_at: string;
    status: GridStatus;
};

export type Schedule = {
    date: string;
    starts_at: string;
    minutes: number;
    blocks: Block[];
};

export type Day = {
    date: string;
    weekday: string;
    day: number;
    long: string;
};
