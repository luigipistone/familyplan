-- Migration: profile is now opened only from the header icon, not as a dashboard
-- or bottom/navigation widget.
USE portale_familyplan;

DELETE FROM dashboard_widgets WHERE widget_key = 'profile';
