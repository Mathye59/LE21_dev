type Props = {
  label: string;
  min: number;
  max: number;
  value: number;
  onChange: (value: number) => void;
  ariaValuetext?: string;
  step?: number;
};

const FilterRange: React.FC<Props> = ({
  label,
  min,
  max,
  value,
  onChange,
  ariaValuetext,
  step = 50,
}) => {
  return (
    <div className="mb-6">
      <label
        htmlFor="rangeSlider"
        className="range"
      >
        {label}-{value}
      </label>
      <input
        type="range"
        id="rangeSlider"
        min={min}
        max={max}
        step={step}
        value={value}
        onChange={(e) => onChange(Number(e.currentTarget.value))}
        aria-valuetext={ariaValuetext}
        className="range"
      />
      <div className="MinMaxSlide">
        <span>{min}</span>
        <span>{max}</span>
      </div>
    </div>
  );
};

export default FilterRange;
