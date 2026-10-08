// Only adjacent weekdays with the same complete set of shifts can share a row.
export function groupWorkingHours(hours = []) {
  return hours.reduce((groups, hour) => {
    const ranges = (hour.ranges ?? []).slice().sort((a, b) => a.start.localeCompare(b.start));
    const signature = JSON.stringify(ranges.map(({ start, end }) => [start, end]));
    const previous = groups.at(-1);
    if (previous && Number.isInteger(hour.day_number)
      && previous.lastDayNumber + 1 === hour.day_number && previous.signature === signature) {
      previous.lastDayNumber = hour.day_number;
      previous.day_name = `${previous.firstDayName} تا ${hour.day_name}`;
    } else {
      groups.push({ ...hour, ranges, signature, firstDayName: hour.day_name, lastDayNumber: hour.day_number });
    }
    return groups;
  }, []);
}

export function placeMap(place) {
  const lat = Number(place.latitude);
  const lng = Number(place.longitude);
  const hasCoordinates = place.latitude !== null && place.latitude !== undefined && place.latitude !== ''
    && place.longitude !== null && place.longitude !== undefined && place.longitude !== ''
    && Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
  const query = hasCoordinates ? `${lat},${lng}` : place.address;
  const coordinates = hasCoordinates ? { lat, lng } : null;

  return {
    directions: query ? `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}` : null,
    coordinates,
    // نشان و بلد فقط با مختصات دقیق کار می کنند (بر خلاف گوگل، آدرس متنی را پشتیبانی نمی کنند)
    directionLinks: directionLinks(coordinates, place.address),
  };
}

/** لینک های مسیریابی در نقشه های پرکاربرد داخلی و گوگل مپ */
export function directionLinks(coordinates, address) {
  const query = coordinates ? `${coordinates.lat},${coordinates.lng}` : address;
  const links = [];

  if (query) {
    links.push({
      key: 'google', label: 'گوگل‌مپ',
      url: `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}`,
    });
  }
  if (coordinates) {
    links.push({
      key: 'neshan', label: 'نشان',
      url: `https://neshan.org/maps/directions?dLat=${coordinates.lat}&dLng=${coordinates.lng}`,
    });
    links.push({
      key: 'balad', label: 'بلد',
      url: `https://balad.ir/directions?dlat=${coordinates.lat}&dlng=${coordinates.lng}`,
    });
  }

  return links;
}
